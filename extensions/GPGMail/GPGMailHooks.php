<?php
/**
 * Hooks for GPGMail extension
 *
 * @file
 * @ingroup Extensions
 */

use GpgLib\GpgLib;
use GpgLib\GpgLibException;
use GpgLib\PgpMime;
use GpgLib\ShellGpgLibFactory;
use MediaWiki\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Hooks for GPGMail extension
 */
class GPGMailHooks {
	/**
	 * Integrate Composer autoloader with MediaWiki autoloading.
	 */
	public static function registerExtension() {
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require_once __DIR__ . '/vendor/autoload.php';
		}
	}

	/**
	 * Add GPG checkbox + key textbox to user preferences.
	 * @param User $user
	 * @param array &$preferences
	 * @return bool
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['gpgmail-enable'] = [
			'type' => 'toggle',
			'label-message' => 'gpgmail-pref-enable',
			'section' => 'personal/email',
		];
		$preferences['gpgmail-key'] = [
			'type' => 'textarea',
			'label-message' => 'gpgmail-pref-key',
			'help-message' => 'gpgmail-pref-key-help',
			'section' => 'personal/email',
			'validation-callback' => 'GPGMailHooks::validateKey',
			'hide-if' => [ '===', 'gpgmail-enable', '' ],
		];
		return true;
	}

	/**
	 * Prevent bulk mailing users who requested encryption
	 * @param MailAddress[] &$to
	 * @return bool
	 */
	public static function onUserMailerSplitTo( &$to ) {
		foreach ( $to as $i => $singleTo ) {
			if ( !$singleTo instanceof MailAddress ) {
				self::getLogger()->warning( 'invalid address: {to} ', [ 'to' => $singleTo ] );
				continue;
			}
			$user = User::newFromName( $singleTo->name );
			if ( $user->getBoolOption( 'gpgmail-enable' ) ) {
				unset( $to[$i] );
			}
		}
		return true;
	}

	/**
	 * @param MailAddress[] $to
	 * @param MailAddress $from
	 * @param string|array &$body Email plaintext or an array with 'text' and 'html' keys
	 * @param Message|string &$error Error message when encryption fails
	 * @return bool
	 */
	public static function onUserMailerTransformContent( array $to, $from, &$body, &$error ) {
		if ( count( $to ) > 1 ) {
			// users who requested encryption have been split out already by UserMailerSplitTo
			return true;
		}
		$user = User::newFromName( $to[0]->name );

		if ( is_array( $body ) ) {
			$status = Status::newGood();
			foreach ( $body as &$content ) {
				$status->merge( self::maybeEncrypt( $content, $user ) );
			}
		} else {
			$status = self::maybeEncrypt( $body, $user );
		}

		if ( !$status->isOK() ) {
			// if the user is emailing themselves, show a descriptive error message
			// otherwise don't reveal to the sender that they are encrypting their email
			if ( $to[0]->address === $from->address ) {
				$error = $status->getWikiText();
			}
			return false;
		}
		return true;
	}

	/**
	 * @param MailAddress[] $to
	 * @param MailAddress $from
	 * @param string &$subject Email subject (not MIME encoded)
	 * @param array &$headers Email headers
	 * @param string &$body Email body (MIME-encoded)
	 * @param Message|string &$error Error message when encryption fails
	 * @return bool
	 */
	public static function onUserMailerTransformMessage( $to, $from,
		&$subject, &$headers, &$body, &$error
	) {
		if ( count( $to ) > 1 ) {
			// users who requested encryption have been split out already by UserMailerSplitTo
			return true;
		}
		$user = User::newFromName( $to[0]->name );

		$status = self::maybeEncryptMime( $headers, $body, $user );

		if ( !$status->isOK() ) {
			// if the user is emailing themselves, show a descriptive error message
			// otherwise don't reveal to the sender that they are encrypting their email
			if ( $to[0]->address === $from->address ) {
				$error = $status->getWikiText();
			}
			return false;
		}
		return true;
	}

	/**
	 * HTMLForm validation callback that checks whether the value is a proper GPG public key.
	 * @param string $value The text of the key
	 * @param array $alldata Data from all form fields
	 * @param HTMLForm $form
	 * @return bool
	 */
	public static function validateKey( $value, $alldata, $form ) {
		if ( !$alldata['gpgmail-enable'] ) {
			return true;
		}

		$gpgLib = self::getGPGLib();
		return $gpgLib->validateKey( $value, GpgLib::KEY_PUBLIC ) ? true : 'Invalid GPG public key';
	}

	/**
	 * Encrypts the message if the target user asked for that.
	 * @param string &$text Text of the message
	 * @param User $user User to whom the message will be sent
	 * @return Status Success or an error message
	 */
	protected static function maybeEncrypt( &$text, $user ) {
		if ( $user->getBoolOption( 'gpgmail-enable' ) && !self::usePgpMime() ) {
			try {
				$text = self::getGPGLib()->encrypt( $text, $user->getOption( 'gpgmail-key' ) );
			} catch ( GpgLibException $e ) {
				return Status::newFatal( new RawMessage( $e->getMessage() ) );
			}
			if ( !$text ) {
				return Status::newFatal( 'gpgmail-encrypt-error' );
			}
		}
		return Status::newGood();
	}

	/**
	 * @param array &$headers Email headers
	 * @param string &$body Email body (MIME-encoded)
	 * @param User $user User to whom the message will be sent
	 * @return Status Success or an error message
	 */
	protected static function maybeEncryptMime( &$headers, &$body, $user ) {
		if ( $user->getBoolOption( 'gpgmail-enable' ) && self::usePgpMime() ) {
			try {
				$pgpMime = new PgpMime( self::getGPGLib() );
				list( $headers, $body ) = $pgpMime->encrypt(
					$headers, $body, $user->getOption( 'gpgmail-key' ) );
			} catch ( GpgLibException $e ) {
				return Status::newFatal( new RawMessage( $e->getMessage() ) );
			}
			if ( !$body ) {
				return Status::newFatal( 'gpgmail-encrypt-error' );
			}
		}
		return Status::newGood();
	}

	/**
	 * When true, PGP/MIME (RFC 3156) should be used, otherwise inline encryption.
	 * @return bool
	 */
	protected static function usePgpMime() {
		global $wgGpgMailUsePgpMime;
		return $wgGpgMailUsePgpMime;
	}

	/**
	 * @return GpgLib
	 */
	protected static function getGPGLib() {
		global $wgGPGMailBinary, $wgGPGMailTempDir;

		$gpgLibFactory = new ShellGpgLibFactory();
		if ( $wgGPGMailBinary ) {
			$gpgLibFactory->setGpgBinary( $wgGPGMailBinary );
		}
		if ( $wgGPGMailTempDir ) {
			$gpgLibFactory->setTempDirFactory( new \GpgLib\TempDirFactory( $wgGPGMailTempDir ) );
		}
		$gpgLibFactory->setLogger( self::getLogger() );

		return $gpgLibFactory->create();
	}

	/**
	 * @return LoggerInterface
	 */
	protected static function getLogger() {
		return LoggerFactory::getInstance( 'gpglib' );
	}
}

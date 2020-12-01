<?php

namespace EncryptedUploads;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\IOException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\File;
use Parser;
use stdClass;
use Title;
use User;

/**
 * Class for EncryptedUploads extension
 *
 * @file
 * @ingroup Extensions
 */
class EncryptedUploads {

	/** @var EncryptedUploads */
	private static $instance;

	/** @var \Wikimedia\Rdbms\Database */
	private $dbr;

	/** @var \Wikimedia\Rdbms\Database */
	private $dbw;

	/**
	 * @return EncryptedUploads
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * EncryptedUploads constructor.
	 */
	public function __construct() {
		$this->dbw = wfGetDB( DB_MASTER );
		// TODO: replace with DB_REPLICA
		$this->dbr = wfGetDB( DB_REPLICA );
	}

	/**
	 * @param string $salt
	 *
	 * @return string
	 */
	public function generateRandomPassword( $salt = '' ) {
		return md5( 'rnd_' . $salt . time() );
	}

	/**
	 * @param string $file
	 * @param        $output
	 * @param string $password
	 *
	 * @return bool
	 */
	public function encryptFile( $file, $output, $password ) {
		try {
			File::encryptFileWithPassword( $file, $output, $password );
		}
		catch ( EnvironmentIsBrokenException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Encryption caused EnvironmentIsBrokenException ' .
											$exception->getMessage() );
			return false;
		}
		catch ( IOException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Encryption caused IOException ' .
											$exception->getMessage() );
			return false;
		}
		catch ( WrongKeyOrModifiedCiphertextException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Encryption caused WrongKeyOrModifiedCiphertextException ' .
											$exception->getMessage() );
			return false;
		}
		return true;
	}

	/**
	 * @param string $file
	 * @param string $output
	 * @param string $password
	 *
	 * @return bool
	 */
	public function decryptFile( $file, $output, $password ) {
		try {
			File::decryptFileWithPassword( $file, $output, $password );
		}
		catch ( EnvironmentIsBrokenException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Decryption caused EnvironmentIsBrokenException ' .
											$exception->getMessage() );
			return false;
		}
		catch ( IOException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Decryption caused IOException ' .
											$exception->getMessage() );
			return false;
		}
		catch ( WrongKeyOrModifiedCiphertextException $exception ) {
			wfDebugLog( 'EncryptedUploads', 'Encryption caused WrongKeyOrModifiedCiphertextException ' .
											$exception->getMessage() );
			return false;
		}
		return true;
	}

	/**
	 * @param int $titleId
	 *
	 * @return bool|stdClass
	 */
	public function getEncrypted( $titleId ) {
		if ( $titleId ) {
			$row = $this->dbr->selectRow( 'encrypted_file', '*', [ 'page_id' => $titleId ] );
			return $row;
		}
		return false;
	}

	/**
	 * @param User $user
	 * @param int $titleId
	 * @param string $password
	 */
	public function setEncrypted( $user, $titleId, $password ) {
		if ( $titleId && $user && $user->getId() ) {
			$data = [
				'page_id' => $titleId,
				'user_id' => $user->getId(),
				'password' => $password
			];
			$this->dbw->upsert( 'encrypted_file', $data, [ 'page_id' ], $data );
		}
	}

	/**
	 * @param int $titleId
	 */
	public function deleteEncrypted( $titleId ) {
		if ( $titleId ) {
			$this->dbw->delete( 'encrypted_file', [ 'page_id' => $titleId ] );
		}
	}

	/**
	 * @param Parser $parser
	 * @param string $text
	 *
	 * @return string
	 */
	public static function isEncryptedParserFunction( $parser, $text ) {
		if ( $text ) {
			$fileTitle = Title::newFromText( $text, NS_FILE );
			if ( $fileTitle ) {
				$result = self::getInstance()->getEncrypted( $fileTitle->getArticleID() );
				if ( $result !== false ) {
					return '1';
				}
			}
		}
		return '';
	}

}

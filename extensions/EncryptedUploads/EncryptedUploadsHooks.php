<?php

namespace EncryptedUploads;

use ApiUpload;
use ApiUsageException;
use Article;
use Html;
use ImagePage;
use LocalFile;
use LogEntry;
use MWException;
use OutputPage;
use Parser;
use Skin;
use SpecialUpload;
use Title;
use UploadFromFile;
use User;
use UserMailer;

/**
 * Hooks for EncryptedUploads extension
 *
 * @file
 * @ingroup Extensions
 */
class EncryptedUploadsHooks {

	private static function getInputHtml( $checked = false ) {
		return Html::openElement( 'tr' ) . Html::openElement( 'td', [ 'align' => 'right' ] ) .
			   Html::label( wfMessage( 'encrypteduploads-input-label' )->text(), 'encrypt' ) .
			   Html::closeElement( 'td' ) . Html::openElement( 'td' ) .
			   Html::check( 'encrypt', $checked ) .
			   Html::rawElement( 'span', [], wfMessage( 'encrypteduploads-input-text' )->text() ) .
			   Html::closeElement( 'td' ) . Html::closeElement( 'tr' );
	}

	/**
	 * @param SpecialUpload $uploadFormObject
	 */
	public static function onUploadFormInitial( $uploadFormObject ) {
		$checked = false;

		$destFile = $uploadFormObject->getRequest()->getText( 'wpDestFile' );
		if ( $destFile ) {
			$title = Title::newFromText( $destFile, NS_FILE );
			if ( $title && $title->getArticleID() &&
				 EncryptedUploads::getInstance()->getEncrypted( $title->getArticleID() ) ) {
				$checked = true;
			}
		}
		$uploadFormObject->uploadFormTextAfterSummary .= self::getInputHtml( $checked );
	}

	/**
	 * @param SpecialUpload $uploadFormObject
	 */
	public static function onUploadFormBeforeProcessing( $uploadFormObject ) {
		$uploadFormObject->uploadFormTextAfterSummary .= self::getInputHtml(
			$uploadFormObject->getRequest()->getCheck( 'encrypt' )
		);
	}

	/**
	 * @param UploadFromFile $file
	 *
	 * @return void
	 */
	public static function onUploadComplete( &$file ) {
		global $wgRequest;

		$encrypt = $wgRequest->getCheck( 'encrypt' );

		wfDebugLog( 'EncryptedUploads', 'Upload complete request is found, checking..' );

		if ( $encrypt ) {

			wfDebugLog( 'EncryptedUploads', 'Upload is marked to be encrypted, generating password..' );

			$generatedPassword = EncryptedUploads::getInstance()
												 ->generateRandomPassword( $wgRequest->getSession()
																					 ->getUser()
																					 ->getName() );

			wfDebugLog( 'EncryptedUploads', 'Generated password: ' . $generatedPassword );

			$file->getLocalFile()->encryption_password = $generatedPassword;
			$sessionKey = 'encryption_file_' . md5( $file->getTitle()->getDBkey() );

			wfDebugLog( 'EncryptedUploads', 'Setting session key ' . $sessionKey );

			$wgRequest->setSessionData( $sessionKey, $generatedPassword );
		}
	}

	/**
	 * @param LocalFile $file
	 * @param bool $reupload
	 * @param bool $newPageContent
	 *
	 * @throws \MWException
	 */
	public static function onFileUpload( $file, $reupload, $newPageContent ) {
		global $wgPasswordSender, $wgPasswordSenderName, $wgEncryptedUploadsSendMail;

		$encryptor = EncryptedUploads::getInstance();

		if ( property_exists( $file, 'encryption_password' ) ) {

			wfDebugLog( 'EncryptedUploads',
				'Found encryption_password property on the file being uploaded!' );

			$generatedPassword = $file->encryption_password;

			wfDebugLog( 'EncryptedUploads', 'Found password: ' . $generatedPassword );

			$path = $file->getRepo()->getBackend()->getLocalReference( [
				'src' => $file->getPath()
			] );

			if ( $path && $path->getPath() ) {

				wfDebugLog( 'EncryptedUploads', 'Found file, renaming..' );

				rename( $path->getPath(), $path->getPath() . '_encrypted_temp' );

				wfDebugLog( 'EncryptedUploads', 'Renamed "' . $path->getPath() . '" to "' .
												$path->getPath() . '_encrypted_temp' . '"' );
				wfDebugLog( 'EncryptedUploads', 'Encrypting...' );

				$result = $encryptor->encryptFile( $path->getPath() .
												   '_encrypted_temp', $path->getPath(), $generatedPassword );

				wfDebugLog( 'EncryptedUploads', 'Deleting old file..' );
				unlink( $path->getPath() . '_encrypted_temp' );

				if ( $result ) {

					wfDebugLog( 'EncryptedUploads', 'Encrypted successfully! Updating database records..' );

					$user = User::newFromId( $file->getUser( 'id' ) );
					$encryptor->setEncrypted( $user, $file->getTitle()
														  ->getArticleID(), $generatedPassword );

					if ( $wgEncryptedUploadsSendMail && $user->getEmail() ) {
						UserMailer::send(
							new \MailAddress( $user->getEmail(), $user->getName() ),
							new \MailAddress( $wgPasswordSender, $wgPasswordSenderName ),
							wfMessage( 'encrypteduploads-mail-subject' )->text(),
							wfMessage( 'encrypteduploads-mail-text',
								$file->getTitle()->getFullURL(), $generatedPassword )->text()
						);
					}

				}

			}

		} elseif ( $encryptor->getEncrypted( $file->getTitle()->getArticleID() ) ) {
			$encryptor->deleteEncrypted( $file->getTitle()->getArticleID() );
		}
	}

	/**
	 * @param ImagePage &$imagePage
	 * @param OutputPage &$out
	 *
	 * @throws MWException
	 */
	public static function onImageOpenShowImageInlineBefore( &$imagePage, &$out ) {
		$title = $imagePage->getTitle();
		if ( $title && $title->getNamespace() === NS_FILE && $title->exists() ) {
			$encryptor = EncryptedUploads::getInstance();

			// TODO: probably needs to be reworked to use session data too
			$data = $encryptor->getEncrypted( $title->getArticleID() );
			if ( $data ) {

				$showSecret = ( (int)$data->user_id === $out->getUser()->getId() ) ||
							  $out->getUser()->isAllowed( 'read-encrypted-files' ) ||
							  self::customRightsCheck( $out->getUser(), $title );

				//$outputDone = true;
				$out->addModules( 'ext.encrypteduploads.main' );
				$templater = new \TemplateParser( __DIR__ . '/templates', true );

				$args = [];
				$args['title'] = wfMessage( 'encrypteduploads-warning-title' )->text();
				$args['text'] = $showSecret ?
					wfMessage( 'encrypteduploads-warning-secret-text', $data->password )->text() :
					wfMessage( 'encrypteduploads-warning-text' )->text();
				$args['placeholder'] = wfMessage( 'encrypteduploads-warning-placeholder' )->text();
				$args['submit'] = wfMessage( 'encrypteduploads-warning-submit' )->text();

				$html = $templater->processTemplate( 'warning_panel', $args );
				$out->addHTML( $html );

			}

		}
	}

	/**
	 * @param User $viewer
	 * @param Title $title
	 *
	 * @return bool
	 */
	public static function customRightsCheck( $viewer, $title ) {

		global $wgEncryptedUploadsSMWBasedRestrictionsEnabled,
			   $wgEncryptedUploadsSMWFilePropertyName,
			   $wgEncryptedUploadsSMWTargetPropertiesNames,
			   $wgEncryptedUploadsSMWFilePropertyNameDeep;

		if( !$wgEncryptedUploadsSMWBasedRestrictionsEnabled ) {
			// Always bow out if the setting is not enabled.
			return false;
		}

		if( class_exists('\SQI\SemanticQueryInterface') ) {

			$userPageText = $viewer->getUserPage()->getBaseTitle()->getBaseText();

			$filePageText = $title->getBaseText();

			$sqi = new \SQI\SemanticQueryInterface();

			// Find a page related to a file
			$fileProp = $sqi->from( $filePageText, NS_FILE )
							->printout( $wgEncryptedUploadsSMWFilePropertyName )
							->toArray();

			$props = array_shift( $fileProp );

			/** @var Title $targetTitle */
			$targetTitle = $props['properties'][ $wgEncryptedUploadsSMWFilePropertyName ][0];

			if( $wgEncryptedUploadsSMWFilePropertyNameDeep ) {
				$sqi->reset();
				$result = $sqi->from( $targetTitle )->printout( $wgEncryptedUploadsSMWFilePropertyNameDeep )->toArray();
				$result = array_shift( $result );
				if( !array_key_exists( $wgEncryptedUploadsSMWFilePropertyNameDeep, $result['properties'] ) ) {
					return false;
				}
				$targetTitle = $result['properties'][$wgEncryptedUploadsSMWFilePropertyNameDeep][0];
			}

			// Query page for users
			$sqi->reset();
			$sqi->from( $targetTitle->getText() );
			foreach ( $wgEncryptedUploadsSMWTargetPropertiesNames as $p ) {
				$sqi->printout( $p );
			}
			$pageUsers = $sqi->toArray();

			if( count($pageUsers) ) {
				$props = array_shift( $pageUsers );
				foreach ( $wgEncryptedUploadsSMWTargetPropertiesNames as $p ) {
					if ( array_key_exists( $p, $props['properties'] ) ) {
						/** @var Title[] $users */
						$users = $props['properties'][$p];
						/** @var Title $user */
						foreach ( $users as $user ) {
							$u = User::newFromName( str_replace("User:", "", $user->getBaseText() ) );
							if ( $u && $u->getId() && $u->getId() === $viewer->getId() ) {
								return true;
							}
						}
					}
				}
			}

		}

		return false;
	}

	/**
	 * @param \DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addExtensionTable( 'encrypted_file', __DIR__ . '/schema/encrypted_file.sql' );
	}

	/**
	 * @param Article &$article
	 * @param User &$user
	 * @param string $reason
	 * @param int $id
	 * @param string $content
	 * @param LogEntry $logEntry
	 */
	public static function onArticleDeleteComplete(
		&$article,
		&$user,
		$reason,
		$id,
		$content,
		$logEntry
	) {
		if ( $article->getTitle()->getNamespace() === NS_FILE ) {
			$encryptor = EncryptedUploads::getInstance();
			if ( $encryptor->getEncrypted( $id ) ) {
				$encryptor->deleteEncrypted( $id );
			}
		}
	}

	/**
	 * @param ApiUpload &$module
	 *
	 * @return bool
	 * @throws ApiUsageException
	 */
	public static function onAPIAfterExecute( &$module ) {
		if ( !( $module instanceof ApiUpload ) || !$module->getRequest()->wasPosted() ) {
			return true;
		}

		$params = $module->extractRequestParams();
		$sessionKey = 'encryption_file_' . md5( $params['filename'] );
		$password = $module->getRequest()->getSessionData( $sessionKey );
		$test = $module->getRequest()->getSessionData( 'test' );

		if ( $password ) {
			$result = $module->getResult();
			$data = $result->getResultData();
			$status = $result->addValue( array( 'encryption' ), 'secret', $password );
		}
	}

	/**
	 * Make sure our messages and scripts for UploadWizard are loaded
	 *
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		if ( $out->getTitle() && $out->getTitle()->getNamespace() === NS_SPECIAL &&
			 $out->getTitle()->isSpecial( 'UploadWizard' ) ) {

			$out->addModules( 'ext.encrypteduploads.uploadwizard' );
		}
	}

	/**
	 * @param Parser &$parser
	 *
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( &$parser ) {
		$parser->setFunctionHook( 'isencrypted', [
			'EncryptedUploads\\EncryptedUploads',
			'isEncryptedParserFunction'
		] );
	}

}

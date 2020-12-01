<?php
/**
 * DOCXServlet.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    $Id: DOCXServlet.php 8919 2013-03-15 15:33:14Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use MediaWiki\MediaWikiServices;

/**
 * UniversalExport DOCXServlet class.
 * @package BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 */
class DOCXServlet {

	/**
	 * Gets a DOMDocument,
	 * searches it for files,
	 * uploads files and markus to webservice and generated DOCX.
	 * @param DOMDocument &$HtmlDOM The source markup
	 * @param string $DOCXTemplatePath
	 * @return string The resulting DOCX as bytes
	 * @throws ConfigException
	 * @throws FatalError
	 * @throws MWException
	 */
	public function createDOCX( &$HtmlDOM, $DOCXTemplatePath ) {
		if ( !file_exists( $DOCXTemplatePath ) ) {
			throw new MWException( $DOCXTemplatePath );
			// TODO: better place?
		}

		$this->findFiles( $HtmlDOM );
		$this->uploadFiles();

		// HINT: http://www.php.net/manual/en/class.domdocument.php#96055
		// But: Formated Output is evil because is will destroy formatting in <pre> Tags!
		$HtmlDOMXML = $HtmlDOM->saveXML( $HtmlDOM->documentElement );

		// Save temporary
		$tmpHtmlFile = BS_DATA_DIR . '/UEModuleDOCX/' . $this->params['document-token'] . '.html';
		$tmpDOCXFile = BS_DATA_DIR . '/UEModuleDOCX/' . $this->params['document-token'] . '.docx';
		file_put_contents( $tmpHtmlFile, $HtmlDOMXML );

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		$options = [
			'method'          => 'POST',
			'timeout'         => 120,
			'followRedirects' => true,
			'sslVerifyHost'   => false,
			'sslVerifyCert'   => false,
			'postData' => [
				'fileType'      => 'template',
				'documentToken' => $this->params['document-token'],
				'templateFile'  => class_exists( 'CURLFile' ) ? new CURLFile( $DOCXTemplatePath ) : '@'
					. $DOCXTemplatePath,
				'wikiId'        => wfWikiID(),
				'secret'        => $config->get(
					'UEModuleDOCXDOCXServiceSecret'
				),
			]
		];

		if ( $config->get( 'TestMode' ) ) {
			$options['postData']['debug'] = "true";
		}

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleDOCXCreateDOCXBeforeSend',
			[
				$this,
				&$options,
				$HtmlDOM
			]
		);

		$HttpEngine = Http::$httpEngine;
		Http::$httpEngine = 'curl';
		// HINT: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-notes
		$request = MWHttpRequest::factory(
				// Tailing slash is important because otherwise Webserver will send
				// "Moved Permanently" and cURL seems to loose POST data when
				// following redirect
				wfExpandUrl( $this->params['backend-url'] . '/UploadAsset/' ),
				$options
		);
		$status = $request->execute();
		if ( !$status->isOK() ) {
			throw new MWException( $status->getMessage() );
		}

		// Now do the rendering
		// We re-send the parameters but this time without the file.
		unset( $options['postData']['templateFile'] );
		unset( $options['postData']['fileType'] );

		$options['postData']['WIKICONTENT'] = $HtmlDOMXML;

		$request = MWHttpRequest::factory(
			wfExpandUrl( $this->params['backend-url'] . '/RenderDOCX/' ),
			$options
		);
		$status = $request->execute();
		if ( !$status->isOK() ) {
			throw new MWException( $status->getMessage() );
		}
		$pdfByteArray = $request->getContent();
		Http::$httpEngine = $HttpEngine;
		if ( $pdfByteArray == false ) {
			wfDebugLog(
				'BS::UEModuleDOCX',
				'DOCXServlet::createDOCX: Failed creating "' . $this->params['document-token'] . '"'
			);
			throw new MWException(
				'DOCXServlet::createDOCX: Failed creating "'
				. $this->params['document-token']
				. '"'
			);
		}

		file_put_contents( $tmpDOCXFile, $pdfByteArray );

		// Remove temporary file
		if ( !$config->get( 'TestMode' ) ) {
			unlink( $tmpHtmlFile );
			unlink( $tmpDOCXFile );
		}

		return $pdfByteArray;
	}

	/**
	 * Uploads all files found in the markup by the "findFiles" method.
	 */
	protected function uploadFiles() {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		foreach ( $this->filesList as $type => $filesList ) {

			// Backwards compatibility to old inconsitent DOCXTemplates
			// (having "STYLESHEET" as type but linnking to "stylesheets")
			// TODO: Make conditional?
			if ( $type == 'IMAGE' ) {      $type = 'images';
			}
			if ( $type == 'STYLESHEET' ) { $type = 'stylesheets';
			}

			$postData = [
				'fileType'      => $type,
				'documentToken' => $this->params['document-token'],
				'wikiId'        => wfWikiID(),
				'secret'        => $config->get(
					'UEModuleDOCXDOCXServiceSecret'
				)
			];

			$errors = [];
			$counter = 0;
			foreach ( $filesList as $fileName => $sFilePath ) {
				if ( file_exists( $sFilePath ) == false ) {
					$errors[] = $sFilePath;
					continue;
				}
				$postData['file' . $counter++] = class_exists( 'CURLFile' ) ? new CURLFile( $sFilePath ) : '@'
					. $sFilePath;
			}

			if ( !empty( $errors ) ) {
				wfDebugLog(
					'BS::UEModuleDOCX',
					'DOCXServlet::uploadFiles: Error trying to fetch files:' . "\n" . var_export( $errors, true )
				);
			}

			MediaWikiServices::getInstance()->getHookContainer()->run(
				'BSUEModuleDOCXUploadFilesBeforeSend',
				[
					$this,
					&$postData,
					$type
				]
			);

			$HttpEngine = Http::$httpEngine;
			Http::$httpEngine = 'curl';
			$response = Http::post(
				$this->params['backend-url'] . '/UploadAsset/',
				[
					'timeout' => 120,
					'followRedirects' => true,
					'sslVerifyHost' => false,
					'sslVerifyCert' => false,
					'postData' => $postData
				]
			);
			Http::$httpEngine = $HttpEngine;

			if ( $response != false ) {
				wfDebugLog(
					'BS::UEModuleDOCX',
					'DOCXServlet::uploadFiles: Successfully added "' . $type . '"'
				);
				wfDebugLog(
					'BS::UEModuleDOCX',
					$response
				);
			} else {
				wfDebugLog(
					'BS::UEModuleDOCX',
					'DOCXServlet::uploadFiles: Failed adding "' . $type . '"'
				);
			}
		}
	}

	/**
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 *
	 * @var array
	 */
	protected $filesList = [];

	/**
	 * The contructor method forthis class.
	 * @param array &$params The params have to contain the key
	 * 'backend-url', with a valid URL to the webservice. They can
	 * contain a key 'soap-connection-options' for the SoapClient constructor
	 * and a key 'resources' with al list of files to upload.
	 * @throws UnexpectedValueException If 'backend-url' is not set or the Webservice is not available.
	 */
	public function __construct( &$params ) {
		$this->params = $params;
		// $this->filesList =  $params['resources'];

		if ( empty( $this->params['backend-url'] ) ) {
			throw new UnexpectedValueException( 'backend-url-not-set' );
		}

		if ( !BsConnectionHelper::urlExists( $this->params['backend-url'] ) ) {
			throw new UnexpectedValueException( 'backend-url-not-valid' );
		}

		// If a slash is last char, remove it.
		if ( substr( $this->params['backend-url'], -1 ) == '/' ) {
			$this->params['backend-url'] = substr( $this->params['backend-url'], 0, -1 );
		}
	}

	/**
	 * Searches the DOM for <img>-Tags and <a> Tags with class 'internal',
	 * resolves the local filesystem path and adds it to $filesList array.
	 * @param DOMDocument &$html The markup to be searched.
	 * @return bool Well, always true.
	 */
	protected function findFiles( &$html ) {
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		// Find all images
		$imageElements = $html->getElementsByTagName( 'img' );
		foreach ( $imageElements as $imageElement ) {
			$srcUrl      = urldecode( $imageElement->getAttribute( 'src' ) );
			$srcFilename = basename( $srcUrl );

			$isThumb = UploadBase::isThumbName( $srcFilename );
			$tmpFilename = $srcFilename;
			if ( $isThumb ) {
				// HINT: Thumbname-to-filename-conversion taken from includes/Upload/UploadBase.php
				// Check for filenames like 50px- or 180px-, these are mostly thumbnails
				$tmpFilename = substr( $tmpFilename, strpos( $tmpFilename, '-' ) + 1 );
			}
			$fileTitle = Title::newFromText( $tmpFilename, NS_FILE );
			$image = $repoGroup->findFile( $fileTitle );

			// TODO: This is a quickfix for MW 1.19+ --> find better solution
			if ( $image instanceof File && $image->exists() ) {
				$fileRepoLocalRef = $image->getRepo()->getLocalReference( $image->getPath() );
				if ( $fileRepoLocalRef !== null ) {
					$absoluteFileSystemPath = $fileRepoLocalRef->getPath();
				}
				$srcFilename = $image->getName();
			} else {
				$absoluteFileSystemPath = $this->getFileSystemPath( $srcUrl );
			}
			// TODO RBV (05.04.12 11:48): Check if urlencode has side effects
			$imageElement->setAttribute( 'src', 'images/' . urlencode( $srcFilename ) );
			$fileName = $srcFilename;
			MediaWikiServices::getInstance()->getHookContainer()->run(
				'BSUEModuleDOCXFindFiles',
				[
					$this,
					$imageElement,
					$absoluteFileSystemPath,
					$fileName,
					'images'
				]
			);
			MediaWikiServices::getInstance()->getHookContainer()->run(
				'BSUEModuleDOCXWebserviceFindFiles',
				[
					$this,
					$imageElement,
					$absoluteFileSystemPath,
					$fileName,
					'images'
				]
			);
			$this->filesList['images'][$fileName] = $absoluteFileSystemPath;
		}

		$DOMXPath = new DOMXPath( $html );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleDOCXAfterFindFiles',
			[
				$this,
				$html,
				&$this->filesList,
				$this->params,
				$DOMXPath
			]
		);
		return true;
	}

	// <editor-fold desc="Helper Methods" defaultstate="collapsed">

	/**
	 * This helper method resolves the local file system path of a found file
	 * @param string $url
	 * @return string The local file system path
	 */
	public function getFileSystemPath( $url ) {
		if ( $url{0} !== '/' || strpos( $url, $this->params['webroot-filesystempath'] ) === 0 ) {
			// not relative to webroot or absolute filesystempath
			return $url;
		}

		$scriptUrlDir = dirname( $_SERVER['SCRIPT_NAME'] );
		$scriptFSDir  = dirname( $_SERVER['SCRIPT_FILENAME'] );
		if ( strpos( $scriptFSDir, $scriptUrlDir ) == 0 ) {
			// detect virtual path (webserver setting)
			$url = '/' . substr( $url, strlen( $scriptUrlDir ) );
		}

		$newUrl = $this->params['webroot-filesystempath'] . $url;
		// TODO RBV (08.02.11 15:56): What about $wgUploadDirectory?
		return $newUrl;
	}

	// </editor-fold>
}

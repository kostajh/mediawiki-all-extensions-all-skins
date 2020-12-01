<?php
/**
 * BsPDFServlet.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 *
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use MediaWiki\MediaWikiServices;

/**
 * UniversalExport BsPDFServlet class.
 * @package BlueSpiceUEModulePDF
 */
class BsPDFServlet {

	/**
	 * Gets a DOMDocument, searches it for files, uploads files and markus to webservice and generated PDF.
	 * @param DOMDocument &$oHtmlDOM The source markup
	 * @return string The resulting PDF as bytes
	 */
	public function createPDF( &$oHtmlDOM ) {
		$this->moveStyleElementsToHead( $oHtmlDOM );
		$this->findFiles( $oHtmlDOM );
		$this->uploadFiles();

		// HINT: http://www.php.net/manual/en/class.domdocument.php#96055
		// But: Formated Output is evil because is will destroy formatting in <pre> Tags!
		$sHtmlDOM = $oHtmlDOM->saveXML( $oHtmlDOM->documentElement );

		// Save temporary
		$status = \BsFileSystemHelper::ensureCacheDirectory( 'UEModulePDF' );
		if ( !$status->isOK() ) {
			throw new \MWException( $status->getMessage() );
		}
		$sTmpHtmlFile = "{$status->getValue()}/{$this->aParams['document-token']}.html";
		$sTmpPDFFile  = "{$status->getValue()}/{$this->aParams['document-token']}.pdf";
		file_put_contents( $sTmpHtmlFile, $sHtmlDOM );

		$aOptions = [
			'timeout' => 120,
			'postData' => [
				'fileType' => '', // Need to stay empty so UploadAsset servlet saves file to document root directory
				'documentToken' => $this->aParams['document-token'],
				'sourceHtmlFile_name' => basename( $sTmpHtmlFile ),
				'sourceHtmlFile' => class_exists( 'CURLFile' ) ? new CURLFile( $sTmpHtmlFile ) : '@' . $sTmpHtmlFile,
				'wikiId' => WikiMap::getCurrentWikiId(),
			]
		];

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		if ( $config->get( 'TestMode' ) ) {
			$aOptions['postData']['debug'] = "true";
		}

		global $bsgUEModulePDFCURLOptions;
		$aOptions = array_merge_recursive( $aOptions, $bsgUEModulePDFCURLOptions );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFCreatePDFBeforeSend',
			[
				$this,
				&$aOptions,
				$oHtmlDOM
			]
		);

		$vHttpEngine = Http::$httpEngine;
		Http::$httpEngine = 'curl';
		// HINT: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-notes
		// Upload HTML source
		// TODO: Handle $sResponse
		$sResponse = Http::post(
			$this->aParams['soap-service-url'] . '/UploadAsset',
			$aOptions
		);

		// Now do the rendering
		// We re-send the parameters but this time without the file.
		unset( $aOptions['postData']['sourceHtmlFile'] );
		unset( $aOptions['postData']['fileType'] );
		// We do not want the request to be multipart/formdata because that's more difficult to handle on Servlet-side
		$aOptions['postData'] = wfArrayToCgi( $aOptions['postData' ] );
		$vPdfByteArray = Http::post(
			$this->aParams['soap-service-url'] . '/RenderPDF',
			$aOptions
		);
		Http::$httpEngine = $vHttpEngine;

		if ( $vPdfByteArray == false ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::createPDF: Failed creating "' . $this->aParams['document-token'] . '"'
			);
		}

		file_put_contents( $sTmpPDFFile, $vPdfByteArray );

		// Remove temporary file
		if ( !$config->get( 'TestMode' ) ) {
			unlink( $sTmpHtmlFile );
			unlink( $sTmpPDFFile );
		}

		return $vPdfByteArray;
	}

	/**
	 * Uploads all files found in the markup by the "findFiles" method.
	 */
	protected function uploadFiles() {
		global $bsgUEModulePDFUploadThreshold;

		foreach ( $this->aFiles as $sType => $aFiles ) {
			// Backwards compatibility to old inconistent PDFTemplates (having
			// "STYLESHEET" as type but linking to "stylesheets")
			// TODO: Make conditional?
			if ( $sType == 'IMAGE' ) {
				$sType = 'images';
			}
			if ( $sType == 'STYLESHEET' ) {
				$sType = 'stylesheets';
			}

			$aPostData = [
				'fileType'	=> $sType,
				'documentToken' => $this->aParams['document-token'],
				'wikiId'        => WikiMap::getCurrentWikiId()
			];

			$aErrors = [];
			$aPostFiles = [];
			$iCounter = 0;
			$iCurrentUploadSize = 0;
			foreach ( $aFiles as $sFileName => $sFilePath ) {
				if ( file_exists( $sFilePath ) == false ) {
					$aErrors[] = $sFilePath;
					continue;
				}

				$iFileSize = filesize( $sFilePath );
				if ( $iCurrentUploadSize >= $bsgUEModulePDFUploadThreshold ) {
					$this->doFilesUpload( array_merge( $aPostFiles, $aPostData ), $aErrors );

					// Reset all loop variables
					$aErrors = [];
					$aPostFiles = [];
					$iCounter = 0;
					$iCurrentUploadSize = $iFileSize;
				}

				$aPostFiles['file' . $iCounter . '_name'] = $sFileName;
				$aPostFiles['file' . $iCounter] = class_exists( 'CURLFile' ) ? new CURLFile( $sFilePath ) : '@' . $sFilePath;
				$iCounter++;
				$iCurrentUploadSize += $iFileSize;
			}
			$this->doFilesUpload( array_merge( $aPostFiles, $aPostData ), $aErrors ); // For last iteration contents
		}
	}

	/**
	 *
	 * @var array
	 */
	protected $aParams = [];

	/**
	 *
	 * @var array
	 */
	protected $aFiles = [];

	/**
	 * The contructor method forthis class.
	 * @param array &$aParams The params have to contain the key
	 * 'soap-service-url', with a valid URL to the webservice. They can
	 * contain a key 'soap-connection-options' for the SoapClient constructor
	 * and a key 'resources' with al list of files to upload.
	 * @throws UnexpectedValueException If 'soap-service-url' is not set or the Webservice is not available.
	 */
	public function __construct( &$aParams ) {
		$this->aParams = $aParams;
		$this->aFiles = $aParams['resources'];

		if ( empty( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-set' );
		}

		if ( !BsConnectionHelper::urlExists( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-valid' );
		}

		// If a slash is last char, remove it.
		if ( substr( $this->aParams['soap-service-url'], -1 ) == '/' ) {
			$this->aParams['soap-service-url'] = substr( $this->aParams['soap-service-url'], 0, -1 );
		}
	}

	/**
	 * @param array $postData
	 * @return array|null
	 */
	public function uploadExternal( $postData ) {
		$postData = array_merge( [
			'documentToken' => $this->aParams['document-token'],
			'wikiId'        => WikiMap::getCurrentWikiId()
		], $postData );

		return $this->doFilesUpload( $postData );
	}

	/**
	 * Searches the DOM for <img>-Tags and <a> Tags with class 'internal',
	 * resolves the local filesystem path and adds it to $aFiles array.
	 * @param DOMDocument &$oHtml The markup to be searched.
	 * @return bool Well, always true.
	 */
	protected function findFiles( &$oHtml ) {
		// Find all images
		$oImageElements = $oHtml->getElementsByTagName( 'img' );
		foreach ( $oImageElements as $oImageElement ) {
			$oFileResolver = new PDFFileResolver( $oImageElement, $this->aParams['webroot-filesystempath'] );

			$sFileName = $oFileResolver->getFileName();
			$sAbsoluteFileSystemPath = $oFileResolver->getAbsoluteFilesystemPath();
			MediaWikiServices::getInstance()->getHookContainer()->run(
				'BSUEModulePDFFindFiles',
				[
					$this,
					$oImageElement,
					&$sAbsoluteFileSystemPath,
					&$sFileName,
					'images'
				]
			);
			MediaWikiServices::getInstance()->getHookContainer()->run(
				'BSUEModulePDFWebserviceFindFiles',
				[
					$this,
					$oImageElement,
					&$sAbsoluteFileSystemPath,
					&$sFileName,
					'images'
				]
			);
			$this->aFiles['images'][$sFileName] = $sAbsoluteFileSystemPath;
		}

		$oDOMXPath = new DOMXPath( $oHtml );

		/*
		 * This is now in template
		//Find all CSS files
		$oLinkElements = $oHtml->getElementsByTagName( 'link' ); // TODO RBV (02.02.11 16:48): Limit to rel="stylesheet" and type="text/css"
		foreach( $oLinkElements as $oLinkElement ) {
			$sHrefUrl = $oLinkElement->getAttribute( 'href' );
			$sHrefFilename           = basename( $sHrefUrl );
			$sAbsoluteFileSystemPath = $this->getFileSystemPath( $sHrefUrl );
			$this->aFiles[ $sAbsoluteFileSystemPath ] = array( $sHrefFilename, 'STYLESHEET' );
			$oLinkElement->setAttribute( 'href', 'stylesheets/'.$sHrefFilename );
		}
		 */

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFAfterFindFiles',
			[
				$this,
				$oHtml,
				&$this->aFiles,
				$this->aParams,
				$oDOMXPath
			]
		);
		return true;
	}

	/**
	 * @param array $aPostData
	 * @param array|null $aErrors
	 * @return array|null
	 */
	protected function doFilesUpload( $aPostData, $aErrors = [] ) {
		global $bsgUEModulePDFCURLOptions;
		$aOptions = [
			'timeout' => 120,
		];
		$aOptions = array_merge_recursive( $aOptions, $bsgUEModulePDFCURLOptions );

		$sType = $aPostData['fileType'];

		if ( !empty( $aErrors ) ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Error trying to fetch files:' . "\n" . var_export( $aErrors, true )
			);
		}

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFUploadFilesBeforeSend',
			[
				$this,
				&$aPostData,
				$sType
			]
		);

		$aOptions['postData'] = $aPostData;

		$vHttpEngine = Http::$httpEngine;
		Http::$httpEngine = 'curl';
		$requestFactory = MediaWikiServices::getInstance()->getHttpRequestFactory();
		$response = $requestFactory->post(
			$this->aParams['soap-service-url'] . '/UploadAsset',
			$aOptions
		);

		Http::$httpEngine = $vHttpEngine;

		if ( $response !== null ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Successfully added "' . $sType . '"'
			);
			$decoded = FormatJson::decode( $response, 1 );
			wfDebugLog( 'BS::UEModulePDF', FormatJson::encode( $decoded, 1 ) );

			return $decoded;
		} else {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Failed adding "' . $sType . '"'
			);
		}

		return null;
	}

	/**
	 * The extensions "TemplateStyles" adds `<style>` elements directly into the `#content` of a
	 * page. The PDF renderer needs them to be in the `<head>`
	 * @param DOMDocument $oHtmlDOM
	 */
	private function moveStyleElementsToHead( $oHtmlDOM ) {
		$headEl = $oHtmlDOM->getElementsByTagName( 'head' )->item( 0 );
		$bodyEl = $oHtmlDOM->getElementsByTagName( 'body' )->item( 0 );
		$styleElsInBody = $bodyEl->getElementsByTagName( 'style' );
		$nonLiveListOfStyleElsInBody = [];
		foreach ( $styleElsInBody as $styleEl ) {
			$nonLiveListOfStyleElsInBody[] = $styleEl;
		}
		foreach ( $nonLiveListOfStyleElsInBody as $styleEl ) {
			$headEl->appendChild( $styleEl );
		}
	}

	// </editor-fold>
}

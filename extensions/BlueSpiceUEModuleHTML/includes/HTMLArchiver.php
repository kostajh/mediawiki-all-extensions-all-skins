<?php
use MediaWiki\MediaWikiServices;

// TODO: decouple from UEModulePDF
class HTMLArchiver extends BsPDFServlet {

	/**
	 * HTMLArchiver constructor.
	 * @param array $params
	 */
	public function __construct( $params ) {
		$this->aParams = $params;
		$this->aFiles = isset( $params['resources'] ) ? $params['resources'] : [];
	}

	/**
	 * @param DOMDocument &$HtmlDOM
	 * @return string
	 */
	public function createPDF( &$HtmlDOM ) {
		return $this->createHTML( $HtmlDOM );
	}

	/**
	 * Gets a DOMDocument, searches it for files and creates a ZIP with files and markup.
	 * @param DOMDocument &$HtmlDOM The source markup
	 * @return string The resulting Zip archive as bytes
	 */
	public function createHTML( &$HtmlDOM ) {
		$this->findFiles( $HtmlDOM );

		// Save temporary
		$status = BsFileSystemHelper::ensureCacheDirectory( 'UEModuleHTML' );
		$tmpZipFile = $status->getValue() . '/' . $this->aParams['document-token'] . '.zip';

		$zip = new ZipArchive();
		$zip->open( $tmpZipFile, ZipArchive::CREATE );
		// TODO: Find solution for encoding issue:
		// https://github.com/owncloud/core/pull/1117
		// http://stackoverflow.com/questions/20600843/php-ziparchive-non-english-filenames-return-funky-filenames-within-archive

		foreach ( $this->aFiles as $type => $filesList ) {

			// Backwards compatibility to old inconsitent PDFTemplates
			// (having "STYLESHEET" as type but linnking to "stylesheets")
			// TODO: Make conditional?
			if ( $type == 'IMAGE' ) {      $type = 'images';
			}
			if ( $type == 'STYLESHEET' ) { $type = 'stylesheets';
			}
			$assetDir = $this->aParams['title'] . '/' . $type;

			$zip->addEmptyDir( $assetDir );

			foreach ( $filesList as $fileName => $filePath ) {
				if ( file_exists( $filePath ) == false ) {
					$errors[] = $filePath;
					continue;
				}
				$zip->addFile( $filePath, $assetDir . '/' . $fileName );
			}

			if ( !empty( $errors ) ) {
				wfDebugLog(
					'BS::UEModuleHTML',
					'HTMLArchiver::createHTML: Error trying to fetch files:' . "\n" . var_export( $errors, true )
				);
			}
		}
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleHTMLCreateHTMLBeforeSend',
			[
				$this,
				&$options,
				$HtmlDOM
			]
		);
		// HINT: http://www.php.net/manual/en/class.domdocument.php#96055
		// But: Formated Output is evil because is will destroy formatting in <pre> Tags!
		$zip->addFromString( $this->aParams['title'] . '/index.html', $HtmlDOM->saveHTML() );
		$zip->close();

		$pdfByteArray = file_get_contents( $tmpZipFile );

		if ( $pdfByteArray == false ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::createPDF: Failed creating "' . $this->aParams['document-token'] . '"'
			);
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		// Remove temporary file
		if ( !$config->get( 'TestMode' ) ) {
			unlink( $tmpZipFile );
		}

		return $pdfByteArray;
	}
}

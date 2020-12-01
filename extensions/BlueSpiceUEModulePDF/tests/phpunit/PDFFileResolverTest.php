<?php

use BlueSpice\Tests\BSApiTestCase;
use MediaWiki\MediaWikiServices;

/**
 * @group medium
 * @group BlueSpice
 * @group BlueSpiceExtensions
 */
class PDFFileResolverTest extends BSApiTestCase {
	protected $aFiles = null;
	protected $oDOM = null;

	protected $aNames = [
		'Test.JPG' => 'test.JPG',
		'WithQS.JPG' => 'test.JPG',
		'Template:Dummy.JPG' => 'dummy.JPG'
	];

	protected function setUp() : void {
		parent::setUp();

		foreach ( $this->aNames as $sName => $sFile ) {
			$this->uploadFile( $sName, $sFile );
		}
		$this->createDOM();
	}

	public function testPDFFileResolver() {
		$oImageElements = $this->oDOM->getElementsByTagName( 'img' );

		$webrootPath = str_replace( '\\', '/', $GLOBALS['IP'] );
		if ( !empty( $GLOBALS['ScriptPath'] ) ) {
			$parts = explode( '/', $webrootPath );
			if ( "/" . array_pop( $parts ) === $GLOBALS['ScriptPath'] ) {
				$webrootPath = implode( '/', $parts );
			}
		}
		foreach ( $oImageElements as $oImageElement ) {
			$oResolver = new PDFFileResolver( $oImageElement, $webrootPath );
			$sFileName = $oResolver->getFileName();
			$sAbsoluteFilesystemPath = $oResolver->getAbsoluteFilesystemPath();

			$this->assertArrayHasKey( $sFileName, $this->aFiles, "File name retrieved is not correct" );
			$this->assertTrue( file_exists( $sAbsoluteFilesystemPath ), "File does not exist in the location retrieved" );
			if ( $sFileName == "Test.JPG" || $sFileName == "WithQS.JPG" ) {
				$this->assertSame( '137', $oImageElement->getAttribute( 'width' ) );
			} elseif ( $sFileName == "Template:Dummy.JPG" ) {
				$this->assertSame( '650', $oImageElement->getAttribute( 'width' ) );
			}
		}
	}

	protected function uploadFile( $sName, $sFile ) {
		$oFileTitle = Title::makeTitleSafe( NS_FILE, $sName );
		$this->oFileTitle = $oFileTitle;
		$sOrigName = __DIR__ . "/data/" . $sFile;
		$oFileObject = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()
			->newFile( $oFileTitle );

		$oFileObject->upload( $sOrigName, '', '' );
		$this->aFiles[ $oFileTitle->getText() ] = $oFileObject;
	}

	protected function createDOM() {
		$oDOM = new DOMDocument();
		foreach ( $this->aFiles as $sFileName => $oFile ) {
			$oAnchor = $oDOM->createElement( 'a' );
			$oFileTitle = $oFile->getOriginalTitle();
			if ( $oFileTitle->getText() !== "WithQS.JPG" ) {
				$oAnchor->setAttribute( 'data-bs-title', $oFileTitle->getFullText() );
			}
			$oImg = $oDOM->createElement( 'img' );
			$oImg->setAttribute( 'src', $oFile->getUrl() );
			if ( $oFileTitle->getText() !== "WithQS.JPG" ) {
				$oImg->setAttribute( 'src', $oFile->getUrl() . '?qs=' );
			}
			$oAnchor->appendChild( $oImg );
			$oDOM->appendChild( $oAnchor );
		}
		$this->oDOM = $oDOM;
	}
}

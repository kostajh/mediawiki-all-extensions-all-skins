<?php
/**
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2014, Harry Burt
 * @license GPL-2.0-or-later
 */

/**
 * @covers SVGMessageGroup
 * @group Database
 */
class SVGMessageGroupTest extends TranslateSvgTestCase {
	public function setUp() : void {
		parent::setUp();
		$this->prepareFile( __DIR__ . '/../data/Speech_bubbles.svg' );
	}

	public function testConstructorFileNotFound() {
		$this->expectException( MWException::class );
		$this->expectExceptionMessage( 'File not found' );
		new SVGMessageGroup( 'DoesNotExist.svg' );
	}

	public function testRegistration() {
		// In order that a lot of the tests function, prepareFile() calls register()
		// but we should check now that it's worked
		$group = MessageGroups::getGroup( self::$name );

		// $group is either of type SVGMessageGroup (success) or null (failure)
		$this->assertInstanceOf( 'SVGMessageGroup', $group );

		// This should be equivalent to running:
		$this->assertInstanceOf( 'SVGMessageGroup', $this->messageGroup );
	}

	public function testGetSourceLanguage() {
		$this->assertEquals(
			'en',
			$this->messageGroup->getSourceLanguage(),
			'No source language has been set, en should be used as the default'
		);
	}

	/**
	 * @dataProvider provideSetSourceLanguage
	 */
	public function testSetSourceLanguage( $to ) {
		$this->messageGroup->setSourceLanguage( $to );
		$this->assertEquals( $to, $this->messageGroup->getSourceLanguage() );
	}

	public function provideSetSourceLanguage() {
		return [ [ 'de' ], [ 'en' ] ];
	}

	public function testGetId() {
		$this->assertEquals( self::$name, $this->messageGroup->getId() );
	}

	public function testGetLabel() {
		$this->assertEquals( self::$name, $this->messageGroup->getLabel() );
	}

	public function testGetNamespace() {
		$this->assertEquals( NS_FILE, $this->messageGroup->getNamespace() );
	}

	public function testGetDescription() {
		$expected = '[[File:' . self::$name . '|thumb|right|upright|275x275px]]' . "\n" .
		'<div style="overflow:auto; padding:2px;">Created during testing</div>';
		$this->assertEquals( $expected, $this->messageGroup->getDescription() . "" );
	}

	public function testGetOnWikiLanguagesBeforeImport() {
		$this->assertCount(
			0,
			$this->messageGroup->getOnWikiLanguages(),
			'Message group is registered but has not been imported yet, so getOnWikiLanguages() ' .
				'should return an empty array'
		);
	}

	public function testImportTranslations() {
		$ret = $this->messageGroup->importTranslations();
		$this->assertTrue( $ret );

		// Normally updating is asynchronous, but need to force the pace for testing
		MessageGroupStats::clearGroup( $this->messageGroup->getId() );
	}

	/**
	 * @group Broken
	 * @see https://phabricator.wikimedia.org/T196555
	 */
	public function testGetOnWikiLanguagesAfterImport() {
		// Clearly this is dependent on the translations having been imported correctly
		// Note that 'tlh-ca' is dropped since it is not supported by MediaWiki.
		$this->assertArrayEquals(
			[ 'de', 'en', 'fr', 'nl' ],
			$this->messageGroup->getOnWikiLanguages()
		);
	}
}

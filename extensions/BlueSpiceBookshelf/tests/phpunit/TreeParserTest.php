<?php

namespace BlueSpice\Bookshelf\Tests;

use BlueSpice\Bookshelf\TreeParser;
use FormatJson;
use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;

/**
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceBookmaker
 * @group BlueSpiceBookshelf
 *
 * @covers \BlueSpice\Bookshelf\TreeParser
 */
class TreeParserTest extends TestCase {

	/**
	 * @covers TreeParser::__construct
	 */
	public function testContructor() {
		$mockConfig = $this->createMock( 'Config' );
		$mockRegistry = $this->createMock( '\BlueSpice\ExtensionAttributeBasedRegistry' );
		$mockRegistry->expects( $this->any() )
			->method( 'getAllValues' )
			->willReturn( [] );

		$parser = new TreeParser( $mockConfig, $mockRegistry );
		$this->assertInstanceOf( TreeParser::class, $parser );
	}

	/**
	 * @covers TreeParser::parseWikiTextList
	 * @dataProvider provideParseWikiTextListData
	 */
	public function testParseWikiTextList( $wikiText, $expectedTree ) {
		$parser = MediaWikiServices::getInstance()->getService( 'BSBookshelfTreeParser' );
		$tree = $parser->parseWikiTextList( $wikiText );
		$this->assertEquals( $expectedTree, $tree, 'Tree structure should be identically' );
	}

	/**
	 *
	 * @return array
	 */
	public function provideParseWikiTextListData() {
		return [
			[
				file_get_contents( __DIR__ . '/data/Book_1.wiki' ),
				FormatJson::decode( file_get_contents( __DIR__ . '/data/Book_1.json' ), true )
			],
			/*[
				file_get_contents( __DIR__ .'/data/Book_2.wiki' ),
				FormatJson::decode( file_get_contents( __DIR__ .'/data/Book_2.json' ), true )
			]*/
		];
	}
}

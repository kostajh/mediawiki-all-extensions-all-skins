<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use CirrusSearch\CirrusDebugOptions;
use CirrusSearch\CirrusSearch;
use CirrusSearch\CirrusTestCase;
use CirrusSearch\SearchConfig;
use MediaWikiTestCase;
use Wikibase\Lexeme\Search\Elastic\LexemeFullTextQueryBuilder;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeFullTextQueryBuilder
 */
class LexemeFullTextQueryBuilderTest extends MediaWikiTestCase {
	use LexemeDescriptionTest;

	/**
	 * @var array search settings for the test
	 */
	private static $ENTITY_SEARCH_CONFIG = [
		'defaultFulltextRescoreProfile' => 'lexeme_fulltext',
	];

	public function setUp() : void {
		parent::setUp();
		if ( !class_exists( 'CirrusSearch' ) ) {
			$this->markTestSkipped( 'CirrusSearch not installed, skipping' );
		}
		$this->setMwGlobals( 'wgLexemeUseCirrus', true );
	}

	public function searchDataProvider() {
		return [
			"work" => [
				"duck",
				__DIR__ . '/../data/lexemeFulltextSearch/simple.expected'
					],
			"id" => [
				' L2-F1 ',
				__DIR__ . '/../data/lexemeFulltextSearch/id.expected'
			]
		];
	}

	/**
	 * @dataProvider searchDataProvider
	 * @param string $searchString
	 * @param string $expected
	 * @throws \ConfigException
	 */
	public function testSearchElastic( $searchString, $expected ) {
		$this->setMwGlobals( [
			'wgLexemeFulltextRescoreProfile' => 'lexeme_fulltext',
		] );

		$config = new SearchConfig();
		$cirrus = new CirrusSearch( $config, CirrusDebugOptions::forDumpingQueriesInUnitTests() );
		$cirrus->setNamespaces( [ 146 ] );
		$result = json_decode( $cirrus->searchText( $searchString )->getValue(), true );
		$this->assertStringStartsWith( LexemeFullTextQueryBuilder::LEXEME_FULL_TEXT_MARKER, $result['__main__']['description'] );

		$actual = CirrusTestCase::encodeFixture( [
			'query' => $result['__main__']['query']['query'],
			'rescore_query' => $result['__main__']['query']['rescore'],
			'highlight' => $result['__main__']['query']['highlight'],
		] );

		$this->assertFileContains( $expected, $actual, CirrusTestCase::canRebuildFixture() );
	}

}

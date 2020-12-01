<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use CirrusSearch\CirrusDebugOptions;
use CirrusSearch\CirrusTestCase;
use Language;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\Lexeme\Search\Elastic\FormSearchEntity;
use Wikibase\Lexeme\Search\Elastic\LexemeSearchEntity;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeSearchEntity
 */
class LexemeCompletionSearchTest extends \MediaWikiTestCase {

	public function setUp() : void {
		parent::setUp();
		if ( !class_exists( 'CirrusSearch' ) ) {
			$this->markTestSkipped( 'CirrusSearch not installed, skipping' );
		}
		$this->setMwGlobals( 'wgLexemeUseCirrus', true );
	}

	/**
	 * @param Language $userLang
	 * @return LexemeSearchEntity
	 */
	private function newEntitySearch( Language $userLang ) {
		$repo = WikibaseRepo::getDefaultInstance();
		return new LexemeSearchEntity(
			new BasicEntityIdParser(),
			new \FauxRequest(),
			$userLang,
			$repo->getLanguageFallbackChainFactory(),
			$repo->getPrefetchingTermLookup(),
			CirrusDebugOptions::forDumpingQueriesInUnitTests()
		);
	}

	/**
	 * @param Language $userLang
	 * @return LexemeSearchEntity
	 */
	private function newFormSearch( Language $userLang ) {
		$repo = WikibaseRepo::getDefaultInstance();
		return new FormSearchEntity(
			new BasicEntityIdParser(),
			new \FauxRequest(),
			$userLang,
			$repo->getLanguageFallbackChainFactory(),
			$repo->getPrefetchingTermLookup(),
			CirrusDebugOptions::forDumpingQueriesInUnitTests()
		);
	}

	public function searchDataProvider() {
		return [
			"simple" => [
				'Duck',
				'simple'
			],
			"byid" => [
				'(L2)',
				'byid'
			],

		];
	}

	/**
	 * @dataProvider searchDataProvider
	 * @param string $term search term
	 * @param string $expected Expected result filename
	 */
	public function testSearchElastic( $term, $expected ) {
		$search = $this->newEntitySearch( Language::factory( 'en' ) );
		$elasticQuery = $search->getRankedSearchResults(
			$term, 'test' /* not used so far */,
			'lexeme', 10, false
		);
		$decodedQuery = json_decode( $elasticQuery, true );
		$decodedQuery = $decodedQuery['__main__'] ?? $decodedQuery;
		unset( $decodedQuery['path'] );
		// T206100
		$this->setIniSetting( 'serialize_precision', 10 );
		$encodedData = CirrusTestCase::encodeFixture( $decodedQuery );

		$this->assertFileContains(
			__DIR__ . "/../data/lexemeCompletionSearch/$expected.expected",
			$encodedData,
			CirrusTestCase::canRebuildFixture() );
	}

	/**
	 * @dataProvider searchDataProvider
	 * @param string $term search term
	 * @param string $expected Expected result filename
	 */
	public function testSearchFormElastic( $term, $expected ) {
		$search = $this->newFormSearch( Language::factory( 'en' ) );
		$elasticQuery = $search->getRankedSearchResults(
			$term, 'test' /* not used so far */,
			'form', 10, false
		);
		$decodedQuery = json_decode( $elasticQuery, true );
		$decodedQuery = $decodedQuery['__main__'] ?? $decodedQuery;
		unset( $decodedQuery['path'] );

		// T206100
		$this->setIniSetting( 'serialize_precision', 10 );
		$encodedData = CirrusTestCase::encodeFixture( $decodedQuery );

		$this->assertFileContains(
			__DIR__ . "/../data/lexemeCompletionSearch/$expected.form.expected",
			$encodedData,
			CirrusTestCase::canRebuildFixture() );
	}

}

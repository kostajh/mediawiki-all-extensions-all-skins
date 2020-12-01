<?php
namespace Wikibase\Lexeme\Search\Elastic\Tests;

use Elastica\Response;
use Elastica\Result;
use Elastica\ResultSet;
use Language;
use MediaWikiTestCase;
use Wikibase\Lexeme\Search\Elastic\LexemeFulltextResult;
use Wikibase\Lexeme\Search\Elastic\LexemeResult;
use Wikibase\Lexeme\Search\Elastic\LexemeResultSet;

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Generic.Files.LineLength.MaxExceeded
// We need long template strings here...
/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeFulltextResult
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeResult
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeResultSet
 */
class LexemeFulltextResultTest extends MediaWikiTestCase {
	use LexemeDescriptionTest;

	/**
	 * Labels for language & categories
	 * Used by LexemeDescriptionTest
	 * @var array
	 */
	private $labels = [
		'Q1' => [
			'en' => 'English',
			'de' => 'Englische',
			'qqx' => 'Anglais',
		],
		'Q2' => [
			'en' => 'noun',
			'de' => 'Substantiv',
			'qqx' => 'nom',
		],
		'Q3' => [
			'en' => 'singular',
			'qqx' => 'singulier'
		],
		'Q4' => [
			'en' => 'plural',
		],
		'Q5' => [
			'en' => 'nominative',
		],
		'Q6' => [
			'ru' => 'настоящее время'
		]
	];

	public function termResultsProvider() {
		return [
			"by lemma" => [
				'en',
				[ 'Q1', 'Q2' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
					],
					'highlight' => [ 'lemma' => [ 'duck' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'title' => 'duck',
					'description' => '<span class="wb-itemlink-description">English, noun</span>'
				]
			],
			"by lemma de" => [
				'de',
				[ 'Q1', 'Q2' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
					],
					'highlight' => [ 'lemma' => [ 'duck' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'title' => 'duck',
					'description' => '<span class="wb-itemlink-description">Englische, Substantiv</span>'
				]
			],
			'by id' => [
				'en',
				[ 'Q1', 'Q2' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
					],
					'highlight' => [ 'title' => [ 'L1' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'title' => 'duck',
					'description' => '<span class="wb-itemlink-description">English, noun</span>'
				]

			],
			'by form id' => [
				'qqx',
				[ 'Q1', 'Q2', 'Q3' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
						'lexeme_forms' => [
							[ 'id' => 'L1-F1', 'representation' => [ 'ducks', 'geese' ] ],
							[
								'id' => 'L1-F2',
								'representation' => [ 'moreducks', 'moregeese' ],
								'features' => [ 'Q3' ],
							],
						],
					],
					'highlight' => [ 'lexeme_forms.id' => [ 'L1-F2' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'formId' => 'L1-F2',
					'representation' => 'moreducks',
					'features' => [ 'Q3' ],
					'title' => 'moreducks',
					'description' =>
						'<span class="wb-itemlink-description">(wikibaselexeme-form-description: singulier, duck, L1, (wikibaselexeme-description: Anglais, nom))</span>'
				]
			],
			'by form repr' => [
				'qqx',
				[ 'Q1', 'Q2', 'Q4' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
						'lexeme_forms' => [
							[
								'id' => 'L1-F1',
								'representation' => [ 'ducks', 'geese' ],
								'features' => [ 'Q4' ],
							],
							[
								'id' => 'L1-F2',
								'representation' => [ 'moreducks', 'moregeese' ],
								'features' => [ 'Q3' ],
							],
						],
					],
					'highlight' => [ 'lexeme_forms.representation' => [ 'ducks' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'formId' => 'L1-F1',
					'representation' => 'ducks',
					'features' => [ 'Q4' ],
					'title' => 'ducks',
					'description' =>
						'<span class="wb-itemlink-description">(wikibaselexeme-form-description: plural, duck, L1, (wikibaselexeme-description: Anglais, nom))</span>'
				]
			],
			'by another form repr' => [
				'qqx',
				[ 'Q1', 'Q2', 'Q3' ],
				[
					'_source' => [
						'title' => 'L1',
						'lexeme_language' => [ 'code' => 'en', 'entity' => 'Q1' ],
						'lexical_category' => 'Q2',
						'lemma' => [ 'duck', 'goose' ],
						'lexeme_forms' => [
							[
								'id' => 'L1-F1',
								'representation' => [ 'ducks', 'geese' ],
								'features' => [ 'Q4' ],
							],
							[
								'id' => 'L1-F2',
								'representation' => [ 'moreducks', 'moregeese' ],
								'features' => [ 'Q3' ],
							],
						],
					],
					'highlight' => [ 'lexeme_forms.representation' => [ 'moregeese' ] ],
				],
				[
					'id' => 'L1',
					'lemma' => 'duck',
					'lang' => 'Q1',
					'langcode' => 'en',
					'category' => 'Q2',
					'formId' => 'L1-F2',
					'representation' => 'moregeese',
					'features' => [ 'Q3' ],
					'title' => 'moregeese',
					'description' =>
						'<span class="wb-itemlink-description">(wikibaselexeme-form-description: singulier, duck, L1, (wikibaselexeme-description: Anglais, nom))</span>'
				]
			],
			'empty results' => [
				'qqx',
				[],
				null,
				[]
			],
		];
	}

	/**
	 * @dataProvider termResultsProvider
	 */
	public function testTransformResult(
		$displayLanguage,
		array $fetchIds,
		$resultData,
		array $expected
	) {
		$termLookupFactory = $this->getTermLookupFactory( $fetchIds, $displayLanguage );

		$res = new LexemeFulltextResult(
			$this->getIdParser(),
			Language::factory( $displayLanguage ),
			$termLookupFactory
		);

		$resultSet = $this->getMockBuilder( ResultSet::class )
			->disableOriginalConstructor()->getMock();
		if ( $resultData === null ) {
			$resultSet->expects( $this->any() )->method( 'getResults' )->willReturn( [] );
		} else {
			$result = new Result( $resultData );
			$resultSet->expects( $this->any() )->method( 'getResults' )->willReturn( [ $result ] );
		}
		$resultSet->expects( $this->any() )
			->method( 'getResponse' )
			->willReturn( new Response( '{}', 200 ) );

		$converted = $res->transformElasticsearchResult( $resultSet );
		if ( empty( $expected ) ) {
			$this->assertCount( 0, $converted );
			return;
		}

		/**
		 * @var LexemeResultSet $converted
		 */
		$this->assertInstanceOf( LexemeResultSet::class, $converted );
		$this->assertCount( 1, $converted );

		$rawResults = $converted->getRawResults();
		$this->assertCount( 1, $rawResults );

		$rawResult = reset( $rawResults );
		// Check raw data
		$this->assertSame( $expected['id'], $rawResult['lexemeId']->getSerialization(),
			'Bad lexeme ID' );
		$this->assertSame( $expected['lemma'], $rawResult['lemma'],
			'Bad lemma match' );
		$this->assertSame( $expected['lang'], $rawResult['lang'],
			'Bad language match' );
		$this->assertSame( $expected['langcode'], $rawResult['langcode'],
			'Bad langcode match' );
		$this->assertSame( $expected['category'], $rawResult['category'],
			'Bad category match' );

		if ( isset( $expected['formId'] ) ) {
			$this->assertSame( $expected['formId'], $rawResult['formId'],
				'Bad form ID match' );
			$this->assertSame( $expected['representation'], $rawResult['representation'],
				'Bad representation match' );
			$this->assertSame( $expected['features'], $rawResult['features'],
				'Bad features match' );
		}

		$results = $converted->extractResults();
		$this->assertCount( 1, $results );

		$result = reset( $results );
		$this->assertInstanceOf( LexemeResult::class, $result );
		/**
		 * @var LexemeResult $result
		 */
		$this->assertSame( $expected['title'], $result->getTitleSnippet(), "Bad title" );
		$this->assertSame( $expected['description'], $result->getTextSnippet(),
			"Bad description" );
	}

}

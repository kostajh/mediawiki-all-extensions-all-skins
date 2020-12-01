<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Tests\FederatedProperties;

use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\Interactors\TermSearchResult;
use Wikibase\Repo\Api\PropertyDataTypeSearchHelper;
use Wikibase\Repo\FederatedProperties\ApiEntitySearchHelper;
use Wikibase\Repo\FederatedProperties\ApiRequestException;
use Wikibase\Repo\FederatedProperties\FederatedPropertiesException;
use Wikibase\Repo\FederatedProperties\GenericActionApiClient;
use Wikibase\Repo\Tests\HttpResponseMockerTrait;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\FederatedProperties\ApiEntitySearchHelper
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class ApiEntitySearchHelperTest extends TestCase {

	use HttpResponseMockerTrait;

	private $responseDataFiles = [
		'api-entity-search-helper-test-data-emptyResponse.json',
		'api-entity-search-helper-test-data-oneResponse.json',
		'api-entity-search-helper-test-data-twoResponse.json',
		'api-entity-search-helper-test-data-entityIdResponse.json',
		'api-entity-search-helper-test-data-multipleEntityIdResponse.json',
		'api-entity-search-helper-test-data-errorResponse.json',
		'api-entity-search-helper-test-data-unexpectedResponse.json',
		'api-entity-search-helper-test-data-filteredResult.json'
	];

	private $data = [];

	protected function setUp(): void {

		parent::setUp();
		// Load data files once at the start of tests rather than for each test case
		foreach ( $this->responseDataFiles as $file ) {
			$content = file_get_contents( __DIR__ . '/../../data/federatedProperties/' . $file );
			$this->data[$file] = json_decode( $content );
		}
	}

	private function getNewApiSearchHelper( $api, $dataTypes = null ) {
		if ( $dataTypes === null ) {
			$dataTypes = WikibaseRepo::getDataTypeDefinitions()->getTypeIds();
		}
		return new ApiEntitySearchHelper( $api, $dataTypes );
	}

	private function setupTestApi( &$params, $langCode, $responseDataFile, $statusCode = 200 ) {
		$params = array_merge( $params, [ 'language' => $langCode, 'uselang' => $langCode, 'format' => 'json' ] );
		$api = $this->createMock( GenericActionApiClient::class );
		$requestParams = $params;
		$requestParams['limit'] = $requestParams['limit'] * ApiEntitySearchHelper::API_SEARCH_MULTIPLIER;
		$api->expects( $this->once() )
			->method( 'get' )
			->with( $requestParams )
			->willReturn( $this->newMockResponse( json_encode( $this->data[ $responseDataFile ] ), $statusCode ) );
		return $api;
	}

	/**
	 * @dataProvider filteringResultsResponseProvider
	 *
	 * @param $params
	 * @param string $responseDataFile
	 * @param $expectedResultsEntityIds
	 * @param $dataTypes
	 * @param string $langCode
	 * @param $shouldThrowError
	 */
	public function testGetRankedSearchResultsFiltering(
		$params,
		$responseDataFile,
		$expectedResultsEntityIds,
		$dataTypes,
		$shouldThrowError,
		$langCode = 'de'
	) {

		$api = $this->setupTestApi( $params, $langCode, $responseDataFile );
		$apiEntitySearchHelper = $this->getNewApiSearchHelper( $api, $dataTypes );

		if ( isset( $shouldThrowError ) ) {
			$this->expectException( $shouldThrowError );
		}

		$results = $apiEntitySearchHelper->getRankedSearchResults(
			$params[ 'search' ],
			$langCode,
			'property',
			$params[ 'limit' ],
			$params[ 'strictlanguage' ]
		);

		$this->assertEquals( count( $expectedResultsEntityIds ), count( $results ) );
		$this->assertEquals( $expectedResultsEntityIds, array_keys( $results ) );
	}

	public function filteringResultsResponseProvider() {

		$file = 'api-entity-search-helper-test-data-filteredResult.json';
		$defaultParams = [
			'action' => 'wbsearchentities',
			'search' => 'P147',
			'type' => 'property',
			'limit' => 3,
			'strictlanguage' => false
		];

		return [
			'filteredStringResponse' => [
				array_merge( $defaultParams, [ 'limit' => 3 ] ),
				$file,
				[ 'P1', 'P4', 'P5' ], // returned entities
				[ 'string' ], // datatypes
				null,
			],
			'filteredEmptyResult' => [
				array_merge( $defaultParams, [ 'limit' => 6 ] ),
				'api-entity-search-helper-test-data-emptyResponse.json',
				[],
				[],
				null,
			],
			'filteredResultOneLessThanLimit8' => [
				array_merge( $defaultParams, [ 'limit' => 8 ] ),
				$file,
				[ 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7' ],
				[ 'string', 'time' ],
				null,
			],
			'filteredResultOneLessThanLimit' => [
				array_merge( $defaultParams, [ 'limit' => 2 ] ),
				$file,
				[ 'P8' ],
				[ 'monolingualtext' ],
				FederatedPropertiesException::class,
			]
		];
	}

	/**
	 * @dataProvider paramsAndExpectedResponseProvider
	 * @param string $responseDataFile
	 * @param int $expectedResultCount
	 * @param array $expectedResultsEntityId
	 * @throws ApiRequestException
	 */
	public function testGetRankedSearchResults( $langCode, $params, $responseDataFile, $expectedResultsEntityIds ) {

		$api = $this->setupTestApi( $params, $langCode, $responseDataFile );
		$apiEntitySearchHelper = $this->getNewApiSearchHelper( $api );

		$responseData = $this->data[ $responseDataFile ];
		$results = $apiEntitySearchHelper->getRankedSearchResults(
			$params[ 'search' ],
			$langCode,
			'property',
			$params[ 'limit' ],
			$params[ 'strictlanguage' ]
		);

		$this->assertEquals( count( $expectedResultsEntityIds ), count( $results ) );
		$this->assertEquals( $expectedResultsEntityIds, array_keys( $results ) );

		foreach ( $expectedResultsEntityIds as $resultId ) {

			$expectedResult = $this->getResponseDataForId( $responseData->search, $resultId );
			$resultToTest = $results[ $resultId ];

			$this->assertTrue( $resultToTest instanceof TermSearchResult );

			if ( $expectedResult->match->type === 'entityId' ) {

				$this->assertEquals(
					new Term(
						'qid', $expectedResult->match->text ),
						$resultToTest->getMatchedTerm()
					);
			} else {

				$this->assertEquals(
					new Term(
						$expectedResult->match->language,
						$expectedResult->match->text
					),
					$resultToTest->getMatchedTerm()
				);
			}
			$this->assertEquals(
				$expectedResult->match->type,
				$resultToTest->getMatchedTermType()
			);
			$this->assertEquals(
				new PropertyId( $expectedResult->id ),
				$resultToTest->getEntityId()
			);
			$this->assertEquals(
				isset( $expectedResult->label ) ? new Term( $langCode, $expectedResult->label ) : null,
				$resultToTest->getDisplayLabel()
			);
			$this->assertEquals(
				isset( $expectedResult->description ) ? new Term( $langCode, $expectedResult->description ) : null,
				$resultToTest->getDisplayDescription()
			);
			$this->assertEquals(
				$expectedResult->datatype,
				$resultToTest->getMetaData()[PropertyDataTypeSearchHelper::DATATYPE_META_DATA_KEY]
			);
		}
	}

	private function getResponseDataForId( array $searchResponses, $resultId ) {
		$searchResponses = array_filter( $searchResponses, function ( $response ) use ( $resultId ) {
			return $response->id === $resultId;
		} );
		if ( count( $searchResponses ) > 1 ) {
			throw new RuntimeException( 'Ambigious search responses for id ' . $resultId );
		}
		return array_shift( $searchResponses );
	}

	/**
	 * @dataProvider invalidParamsAndUnexpectedResponseProvider
	 * @param string $responseDataFile
	 * @param int $expectedResultCount
	 * @param array $expectedResultsEntityId
	 */
	public function testApiResponseStructureIsValid( $langCode, $params, $responseDataFile, $statusCode ) {
		$api = $this->setupTestApi( $params, $langCode, $responseDataFile, $statusCode );
		$apiEntitySearchHelper = $this->getNewApiSearchHelper( $api );
		try {
			$apiEntitySearchHelper->getRankedSearchResults(
				$params[ 'search' ],
				$langCode,
				'property',
				$params[ 'limit' ],
				$params[ 'strictlanguage' ]

			);
		} catch ( ApiRequestException $exception ) {
			$this->assertEquals( $exception->getMessage(), 'Unexpected response output' );
		}
	}

	/**
	 * @return array [ searchlang, searchParams[], responseDataFile, responseStatusCode ]
	 */
	public function invalidParamsAndUnexpectedResponseProvider() {
		return [
			'errorResponse' => [
				'xyz',
				[
					'action' => 'wbsearchentities',
					'search' => 'foo',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-errorResponse.json',
				400
			],
			'unexpectedResponse' => [
				'en',
				[
					'action' => 'wbsearchentities',
					'search' => 'foo',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-unexpectedResponse.json',
				null
			]
		];
	}

	/**
	 * @return array [ searchlang, searchParams[], responseDataFile, expectedResultEntityIds[] ]
	 */
	public function paramsAndExpectedResponseProvider() {
		return [
			'emptyResponse' => [
				'en',
				[
					'action' => 'wbsearchentities',
					'search' => 'foo',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-emptyResponse.json',
				[],
			],
			'twoResponse' => [
				'en',
				[
					'action' => 'wbsearchentities',
					'search' => 'publication date',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-twoResponse.json',
				[ 'P577', 'P14' ],
			],
			'oneReponse' => [
				'de',
				[
					'action' => 'wbsearchentities',
					'search' => 'Publikationsdatum',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-oneResponse.json',
				[ 'P14' ],
			],
			'entityIdResponse' => [
				'de',
				[
					'action' => 'wbsearchentities',
					'search' => 'P31',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-entityIdResponse.json',
				[ 'P31' ],
			],
			'mixedEntityIdResponse' => [
				'de',
				[
					'action' => 'wbsearchentities',
					'search' => 'P147',
					'type' => 'property',
					'limit' => 10,
					'strictlanguage' => false
				],
				'api-entity-search-helper-test-data-multipleEntityIdResponse.json',
				[ 'P147', 'P160020' ]
			],
		];
	}

}

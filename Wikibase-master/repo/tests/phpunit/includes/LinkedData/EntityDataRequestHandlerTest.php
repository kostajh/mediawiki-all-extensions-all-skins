<?php

namespace Wikibase\Repo\Tests\LinkedData;

use DataValues\Serializers\DataValueSerializer;
use DerivativeContext;
use FauxRequest;
use FauxResponse;
use HashSiteStore;
use HtmlCacheUpdater;
use HttpError;
use MediaWikiIntegrationTestCase;
use OutputPage;
use Psr\Log\NullLogger;
use RequestContext;
use Title;
use Wikibase\DataAccess\EntitySource;
use Wikibase\DataAccess\EntitySourceDefinitions;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SerializerFactory;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Content\EntityContentFactory;
use Wikibase\Repo\LinkedData\EntityDataFormatProvider;
use Wikibase\Repo\LinkedData\EntityDataRequestHandler;
use Wikibase\Repo\LinkedData\EntityDataSerializationService;
use Wikibase\Repo\LinkedData\EntityDataUriManager;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\LinkedData\EntityDataRequestHandler
 *
 * @group Database
 *
 * @group Wikibase
 * @group WikibaseEntityData
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class EntityDataRequestHandlerTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var Title
	 */
	private $interfaceTitle;

	/**
	 * @var int
	 */
	private $obLevel;

	protected function setUp(): void {
		parent::setUp();

		$this->interfaceTitle = Title::newFromText( "Special:EntityDataRequestHandlerTest" );
		// ensure the namespace name doesn’t get translated
		$this->setMwGlobals( 'wgLanguageCode', 'qqx' );

		$this->obLevel = ob_get_level();
	}

	protected function tearDown(): void {
		$obLevel = ob_get_level();

		while ( ob_get_level() > $this->obLevel ) {
			ob_end_clean();
		}

		if ( $obLevel !== $this->obLevel ) {
			$this->fail( "Test changed output buffer level: was {$this->obLevel} before test, but $obLevel after test." );
		}

		parent::tearDown();
	}

	/**
	 * @return EntityDataRequestHandler
	 */
	protected function newHandler() {
		global $wgScriptPath;

		$mockRepository = EntityDataTestProvider::getMockRepository();

		$dataTypeLookup = $this->createMock( PropertyDataTypeLookup::class );
		$dataTypeLookup->expects( $this->any() )
			->method( 'getDataTypeIdForProperty' )
			->will( $this->returnValue( 'string' ) );

		$entityContentFactory = $this->createMock( EntityContentFactory::class );
		// general EntityTitleLookup interface
		$entityContentFactory->expects( $this->any() )
			->method( 'getTitleForId' )
			->will( $this->returnCallback( function( EntityId $id ) {
				return Title::newFromText( $id->getEntityType() . ':' . $id->getSerialization() );
			} ) );
		// EntityContentFactory-specific method – should be unused since we configure no page props
		$entityContentFactory->expects( $this->never() )
			->method( 'newFromEntity' );

		$entityDataFormatProvider = new EntityDataFormatProvider();
		$serializerFactory = new SerializerFactory(
			new DataValueSerializer(),
			SerializerFactory::OPTION_SERIALIZE_MAIN_SNAKS_WITHOUT_HASH +
			SerializerFactory::OPTION_SERIALIZE_REFERENCE_SNAKS_WITHOUT_HASH
		);

		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		// Note: We are testing with the actual RDF bindings. These should not change for well
		// known data types. Mocking the bindings would be nice, but is complex and not needed.
		$rdfBuilder = $wikibaseRepo->getValueSnakRdfBuilderFactory();

		$service = new EntityDataSerializationService(
			$mockRepository,
			$entityContentFactory,
			new InMemoryDataTypeLookup(),
			$rdfBuilder,
			$wikibaseRepo->getEntityRdfBuilderFactory(),
			$entityDataFormatProvider,
			$serializerFactory,
			$serializerFactory->newItemSerializer(),
			new HashSiteStore(),
			new RdfVocabulary(
				[ 'test' => EntityDataSerializationServiceTest::URI_BASE ],
				[ 'test' => EntityDataSerializationServiceTest::URI_DATA ],
				new EntitySourceDefinitions( [
					new EntitySource(
						'test',
						'testdb',
						[ 'item' => [ 'namespaceId' => 1200, 'slot' => 'main' ] ],
						EntityDataSerializationServiceTest::URI_BASE,
						'wd',
						'',
						''
					)
				], new EntityTypeDefinitions( [] ) ),
				'test',
				[ 'test' => 'wd' ],
				[ 'test' => '' ]
			)
		);

		$entityDataFormatProvider->setAllowedFormats(
			[
				// using the API
				'json', // default
				'php',

				// using purtle
				'rdfxml',
				'n3',
				'turtle',
				'ntriples',
				'jsonld',
			]
		);

		$extensions = [
			// using the API
			'json' => 'json', // default
			'php' => 'php',

			// using purtle
			'rdfxml' => 'rdf',
			'n3' => 'n3',
			'turtle' => 'ttl',
			'ntriples' => 'n3',
			'jsonld' => 'jsonld',
		];

		$uriManager = new EntityDataUriManager(
			$this->interfaceTitle,
			$extensions,
			[
				// “Special” needs no translation because we override the content language
				$wgScriptPath . '/index.php?title=Special:EntityDataRequestHandlerTest' .
				'/{entity_id}.json&revision={revision_id}',
			],
			$entityContentFactory
		);
		$mockHtmlCacheUpdater = $this->createMock( HtmlCacheUpdater::class );

		$entityTypesWithoutRdfOutput = [ 'property' ];

		$handler = new EntityDataRequestHandler(
			$uriManager,
			$mockHtmlCacheUpdater,
			WikibaseRepo::getEntityIdParser(),
			$mockRepository,
			$mockRepository,
			$service,
			$entityDataFormatProvider,
			new NullLogger(),
			$entityTypesWithoutRdfOutput,
			'json',
			1800,
			false,
			null
		);

		return $handler;
	}

	/**
	 * @param array $params
	 * @param string[] $headers
	 *
	 * @return OutputPage
	 */
	protected function makeOutputPage( array $params, array $headers ) {
		// construct request
		$request = new FauxRequest( $params );
		$request->setRequestURL( 'https://repo.example/wiki/Special:EntityData/Q1.ttl' );
		$request->response()->header( 'Status: 200 OK', true, 200 ); // init/reset

		foreach ( $headers as $name => $value ) {
			$request->setHeader( strtoupper( $name ), $value );
		}

		// construct Context and OutputPage
		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setRequest( $request );
		$context->setLanguage( 'qqx' );

		$output = new OutputPage( $context );
		$output->setTitle( $this->interfaceTitle );
		$context->setOutput( $output );

		return $output;
	}

	public function handleRequestProvider() {
		return EntityDataTestProvider::provideHandleRequest();
	}

	/**
	 * @dataProvider handleRequestProvider
	 *
	 * @param string $subpage The subpage to request (or '')
	 * @param array  $params  Request parameters
	 * @param array  $headers  Request headers
	 * @param string $expectedOutput Regex to match the output against.
	 * @param int $expectedStatusCode Expected HTTP status code.
	 * @param string[] $expectedHeaders Expected HTTP response headers.
	 */
	public function testHandleRequest(
		$subpage,
		array $params,
		array $headers,
		$expectedOutput,
		$expectedStatusCode = 200,
		array $expectedHeaders = []
	) {
		$output = $this->makeOutputPage( $params, $headers );
		$request = $output->getRequest();

		/** @var FauxResponse $response */
		$response = $request->response();

		// construct handler
		$handler = $this->newHandler();

		try {
			ob_start();
			$handler->handleRequest( $subpage, $request, $output );

			if ( $output->getRedirect() !== '' ) {
				// hack to apply redirect to web response
				$output->output();
			}

			$text = ob_get_contents();
			ob_end_clean();

			$this->assertEquals( $expectedStatusCode, $response->getStatusCode(), 'status code' );
			$this->assertRegExp( $expectedOutput, $text, 'output' );

			foreach ( $expectedHeaders as $name => $exp ) {
				$value = $response->getHeader( $name );
				$this->assertNotNull( $value, "header: $name" );
				$this->assertIsString( $value, "header: $name" );
				$this->assertRegExp( $exp, $value, "header: $name" );
			}
		} catch ( HttpError $e ) {
			ob_end_clean();
			$this->assertEquals( $expectedStatusCode, $e->getStatusCode(), 'status code' );
			$this->assertRegExp( $expectedOutput, $e->getHTML(), 'error output' );
		}

		// We always set "Access-Control-Allow-Origin: *"
		$this->assertSame( '*', $response->getHeader( 'Access-Control-Allow-Origin' ) );
	}

	public function testHandleRequestWith304() {
		$output = $this->makeOutputPage( [], [ 'If-Modified-Since' => '20131213141516' ] );
		$request = $output->getRequest();

		/** @var FauxResponse $response */
		$response = $request->response();

		// construct handler
		$handler = $this->newHandler();
		$handler->handleRequest( 'Q42.json', $request, $output );
		$text = $output->output( true );

		$this->assertSame( 304, $response->getStatusCode(), 'status code' );
		$this->assertSame( '', $text, 'output' );

		// We always set "Access-Control-Allow-Origin: *"
		$this->assertSame( '*', $response->getHeader( 'Access-Control-Allow-Origin' ) );
	}

	public function provideHttpContentNegotiation() {
		$q13 = new ItemId( 'Q13' );
		return [
			'No Accept Header' => [
				$q13,
				[], // headers
				'Q13.json'
			],
			'Accept Header without weights' => [
				$q13,
				[ 'ACCEPT' => '*/*, text/html, text/x-wiki' ], // headers
				'Q13'
			],
			'Accept Header with weights' => [
				$q13,
				[ 'ACCEPT' => 'text/*; q=0.5, text/json; q=0.7, application/rdf+xml; q=0.8' ], // headers
				'Q13.rdf'
			],
		];
	}

	/**
	 * @dataProvider provideHttpContentNegotiation
	 *
	 * @param EntityId $id
	 * @param array $headers Request headers
	 * @param string $expectedRedirectSuffix Expected suffix of the HTTP Location header.
	 *
	 * @throws HttpError
	 */
	public function testHttpContentNegotiation(
		EntityId $id,
		array $headers,
		$expectedRedirectSuffix
	) {
		/** @var FauxResponse $response */
		$output = $this->makeOutputPage( [], $headers );
		$request = $output->getRequest();

		$handler = $this->newHandler();
		$handler->httpContentNegotiation( $request, $output, $id );

		$this->assertStringEndsWith(
			$expectedRedirectSuffix,
			$output->getRedirect(),
			'redirect target'
		);
	}

	public function testCacheHeaderIsSetWithRevision() {
		$params = [ 'revision' => EntityDataTestProvider::ITEM_REVISION_ID ];
		$subpage = 'Q42.json';
		$output = $this->makeOutputPage( $params, [] );
		/** @var FauxRequest $request */
		$request = $output->getRequest();
		'@phan-var FauxRequest $request';
		$request->setRequestUrl(
			$this->interfaceTitle->getSubpage( $subpage )->getLocalURL( $params ) );

		/** @var FauxResponse $response */
		$response = $request->response();

		$handler = $this->newHandler();
		ob_start();
		$handler->handleRequest( $subpage, $request, $output );
		ob_end_clean();

		$this->assertStringContainsString( 'public', $response->getHeader( 'Cache-Control' ) );
	}

	public function testCacheHeaderIsNotSetWithoutRevision() {
		$params = [];
		$subpage = 'Q42.json';
		$output = $this->makeOutputPage( $params, [] );
		$request = $output->getRequest();
		/** @var FauxRequest $request */
		$request = $output->getRequest();
		'@phan-var FauxRequest $request';
		$request->setRequestUrl(
			$this->interfaceTitle->getSubpage( $subpage )->getLocalURL( $params ) );

		/** @var FauxResponse $response */
		$response = $request->response();

		$handler = $this->newHandler();
		ob_start();
		$handler->handleRequest( $subpage, $request, $output );
		ob_end_clean();

		$this->assertStringContainsString( 'no-cache', $response->getHeader( 'Cache-Control' ) );
		$this->assertStringContainsString( 'private', $response->getHeader( 'Cache-Control' ) );
	}

	//TODO: test canHandleRequest
	//TODO: test getCanonicalFormat
	//TODO: test ALL the things!
}

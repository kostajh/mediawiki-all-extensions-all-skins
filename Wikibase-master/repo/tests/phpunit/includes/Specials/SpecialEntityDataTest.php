<?php

namespace Wikibase\Repo\Tests\Specials;

use DataValues\Serializers\DataValueSerializer;
use FauxRequest;
use FauxResponse;
use HashSiteStore;
use HtmlCacheUpdater;
use HttpError;
use Language;
use OutputPage;
use Psr\Log\NullLogger;
use SpecialPage;
use SpecialPageTestBase;
use Title;
use Wikibase\DataAccess\EntitySource;
use Wikibase\DataAccess\EntitySourceDefinitions;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\SerializerFactory;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Content\EntityContentFactory;
use Wikibase\Repo\LinkedData\EntityDataFormatProvider;
use Wikibase\Repo\LinkedData\EntityDataRequestHandler;
use Wikibase\Repo\LinkedData\EntityDataSerializationService;
use Wikibase\Repo\LinkedData\EntityDataUriManager;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikibase\Repo\Specials\SpecialEntityData;
use Wikibase\Repo\Tests\LinkedData\EntityDataTestProvider;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\Specials\SpecialEntityData
 * @covers \Wikibase\Repo\Specials\SpecialWikibasePage
 *
 * @group Database
 *
 * @group Wikibase
 * @group SpecialPage
 * @group WikibaseSpecialPage
 * @group WikibaseEntityData
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class SpecialEntityDataTest extends SpecialPageTestBase {

	const URI_BASE = 'http://acme.test/';
	const URI_DATA = 'http://data.acme.test/';

	protected function newSpecialPage() {
		$page = new SpecialEntityData(
			$this->newRequestHandler(),
			$this->newEntityDataFormatProvider()
		);

		// why is this needed?
		$page->getContext()->setOutput( new OutputPage( $page->getContext() ) );
		$page->getContext()->setLanguage( 'qqx' );

		return $page;
	}

	private function newRequestHandler() {
		$mockRepository = EntityDataTestProvider::getMockRepository();

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

		$dataTypeLookup = $this->createMock( PropertyDataTypeLookup::class );
		$dataTypeLookup->expects( $this->any() )
			->method( 'getDataTypeIdForProperty' )
			->will( $this->returnValue( 'string' ) );

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

		$serializationService = new EntityDataSerializationService(
			$mockRepository,
			$entityContentFactory,
			$dataTypeLookup,
			$rdfBuilder,
			$wikibaseRepo->getEntityRdfBuilderFactory(),
			$entityDataFormatProvider,
			$serializerFactory,
			$serializerFactory->newItemSerializer(),
			new HashSiteStore(),
			new RdfVocabulary(
				[ 'test' => self::URI_BASE ],
				[ 'test' => self::URI_DATA ],
				new EntitySourceDefinitions( [
					new EntitySource(
						'test',
						'testdb',
						[ 'item' => [ 'namespaceId' => 123, 'slot' => 'main' ] ],
						self::URI_BASE,
						'',
						'',
						''
					)
				], new EntityTypeDefinitions( [] ) ),
				'test',
				[ 'test' => '' ],
				[ 'test' => '' ]
			)
		);

		$formats = [ 'json', 'rdfxml', 'ntriples', 'turtle' ];
		$entityDataFormatProvider->setAllowedFormats( $formats );

		$defaultFormat = 'rdf';
		$supportedExtensions = array_combine( $formats, $formats );

		$title = SpecialPage::getTitleFor( 'EntityData' );

		$uriManager = new EntityDataUriManager(
			$title,
			$supportedExtensions,
			[],
			$entityContentFactory
		);
		$mockHtmlCacheUpdater = $this->createMock( HtmlCacheUpdater::class );

		$useCdn = false;
		$apiFrameOptions = 'DENY';

		$entityTypesWithRdfOutputAvailable = [ 'property' ];

		return new EntityDataRequestHandler(
			$uriManager,
			$mockHtmlCacheUpdater,
			WikibaseRepo::getEntityIdParser(),
			$mockRepository,
			$mockRepository,
			$serializationService,
			$entityDataFormatProvider,
			new NullLogger(),
			$entityTypesWithRdfOutputAvailable,
			$defaultFormat,
			0,
			$useCdn,
			$apiFrameOptions
		);
	}

	public function provideExecute() {
		$cases = EntityDataTestProvider::provideHandleRequest();

		foreach ( $cases as $n => $case ) {
			// cases with no ID given will no longer fail be show an html form

			if ( $case[0] === '' && !isset( $case[1]['id'] ) ) {
				$cases[$n][3] = '!<p>!'; // output regex //TODO: be more specific
				$cases[$n][4] = 200; // http code
				$cases[$n][5] = []; // response headers
			}
		}
		return $cases;
	}

	/**
	 * @dataProvider provideExecute
	 *
	 * @param string $subpage The subpage to request (or '')
	 * @param array  $params  Request parameters
	 * @param array  $headers  Request headers
	 * @param string $expRegExp   Regex to match the output against.
	 * @param int    $expCode     Expected HTTP status code
	 * @param array  $expHeaders  Expected HTTP response headers
	 */
	public function testExecute(
		$subpage,
		array $params,
		array $headers,
		$expRegExp,
		$expCode = 200,
		array $expHeaders = []
	) {
		$request = new FauxRequest( $params );
		$request->setRequestURL( $this->newSpecialPage()->getPageTitle( $subpage )->getLocalURL( $params ) );
		$request->response()->header( 'Status: 200 OK', true, 200 ); // init/reset

		foreach ( $headers as $name => $value ) {
			$request->setHeader( strtoupper( $name ), $value );
		}

		try {
			/** @var FauxResponse $response */
			list( $output, $response ) = $this->executeSpecialPage( $subpage, $request );

			$this->assertEquals( $expCode, $response->getStatusCode(), "status code" );
			$this->assertRegExp( $expRegExp, $output, "output" );

			foreach ( $expHeaders as $name => $exp ) {
				$value = $response->getHeader( $name );
				$this->assertNotNull( $value, "header: $name" );
				$this->assertIsString( $value, "header: $name" );
				$this->assertRegExp( $exp, $value, "header: $name" );
			}
		} catch ( HttpError $e ) {
			$this->assertEquals( $expCode, $e->getStatusCode(), "status code" );
			$this->assertRegExp( $expRegExp, $e->getHTML(), "error output" );
		}
	}

	private function newEntityDataFormatProvider() {
		$entityDataFormatProvider = new EntityDataFormatProvider();
		$entityDataFormatProvider->setAllowedFormats( [ 'json', 'rdfxml', 'ntriples' ] );

		return $entityDataFormatProvider;
	}

	public function testEntityDataFormatProvider() {
		$this->setContentLang( Language::factory( 'en' ) );
		$request = new FauxRequest();
		$request->response()->header( 'Status: 200 OK', true, 200 ); // init/reset

		list( $output, ) = $this->executeSpecialPage( '', $request );

		$expected = '(wikibase-entitydata-text: json(comma-separator)nt(comma-separator)' .
			'rdf(comma-separator)html)';
		$this->assertStringContainsString( $expected, $output, "output" );
	}

}

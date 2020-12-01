<?php

namespace Wikibase\Repo\Tests\Rdf;

use MediaWikiIntegrationTestCase;
use SiteLookup;
use Title;
use Wikibase\DataAccess\EntitySource;
use Wikibase\DataAccess\EntitySourceDefinitions;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Repo\Content\EntityContent;
use Wikibase\Repo\Content\EntityContentFactory;
use Wikibase\Repo\Rdf\DedupeBag;
use Wikibase\Repo\Rdf\EntityRdfBuilderFactory;
use Wikibase\Repo\Rdf\HashDedupeBag;
use Wikibase\Repo\Rdf\NullEntityRdfBuilder;
use Wikibase\Repo\Rdf\PropertyRdfBuilder;
use Wikibase\Repo\Rdf\RdfBuilder;
use Wikibase\Repo\Rdf\RdfProducer;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikibase\Repo\Rdf\SiteLinksRdfBuilder;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Purtle\NTriplesRdfWriter;
use Wikimedia\Purtle\RdfWriter;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \Wikibase\Repo\Rdf\RdfBuilder
 *
 * @group Wikibase
 * @group WikibaseRdf
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Stas Malyshev
 */
class RdfBuilderTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var NTriplesRdfTestHelper
	 */
	private $helper;

	protected function setUp(): void {
		parent::setUp();

		$this->helper = new NTriplesRdfTestHelper(
			new RdfBuilderTestData(
				__DIR__ . '/../../data/rdf/entities',
				__DIR__ . '/../../data/rdf/RdfBuilder'
			)
		);

		$this->helper->setAllBlanksEqual( false );
	}

	/**
	 * Initialize repository data
	 *
	 * @return RdfBuilderTestData
	 */
	private function getTestData() {
		return $this->helper->getTestData();
	}

	/**
	 * @return EntityTitleLookup
	 */
	private function getEntityTitleLookup() {
		$entityTitleLookup = $this->createMock( EntityTitleLookup::class );
		$entityTitleLookup->expects( $this->any() )
			->method( 'getTitleForId' )
			->will( $this->returnCallback( function ( EntityId $entityId ) {
				return Title::newFromText( $entityId->getSerialization() );
			} ) );

		return $entityTitleLookup;
	}

	/**
	 * Returns the mapping of entity types used in tests to callbacks instantiating EntityRdfBuilder
	 * instances, that are configured to use services configured for test purposes (e.g. SiteLookup).
	 *
	 * @see EntityTypeDefinitions::getRdfBuilderFactoryCallbacks
	 *
	 * TODO: move to RdfBuilderTestData?
	 *
	 * @param SiteLookup $siteLookup
	 *
	 * @return callable[]
	 */
	private function getRdfBuilderFactoryCallbacks( SiteLookup $siteLookup ) {
		return [
			'item' => function(
				$flavorFlags,
				RdfVocabulary $vocabulary,
				RdfWriter $writer,
				$mentionedEntityTracker,
				$dedupe
			) use ( $siteLookup ) {
				if ( $flavorFlags & RdfProducer::PRODUCE_SITELINKS ) {
					$sites = $siteLookup->getSites();
					$builder = new SiteLinksRdfBuilder( $vocabulary, $writer, $sites );
					$builder->setDedupeBag( $dedupe );
					return $builder;
				}
				return new NullEntityRdfBuilder();
			},
			'property' => function(
				$flavorFlags,
				RdfVocabulary $vocabulary,
				RdfWriter $writer
			) {
				return new PropertyRdfBuilder(
					$vocabulary,
					$writer,
					WikibaseRepo::getDataTypeDefinitions()->getRdfDataTypes()
				);
			}
		];
	}

	/**
	 * @param int           $produce One of the RdfProducer::PRODUCE_... constants.
	 * @param DedupeBag     $dedup
	 * @param RdfVocabulary $vocabulary
	 *
	 * @return RdfBuilder
	 */
	private function newRdfBuilder(
		$produce,
		DedupeBag $dedup = null,
		RdfVocabulary $vocabulary = null
	) {
		if ( $dedup === null ) {
			$dedup = new HashDedupeBag();
		}

		$siteLookup = $this->getTestData()->getSiteLookup();

		$entityContentFactory = $this->createMock( EntityContentFactory::class );
		// this default EntityContentFactory expects that page props are disabled;
		// for tests with page props, override it with TestingAccessWrapper afterwards
		$entityContentFactory->expects( $this->never() )
			->method( 'newFromEntity' );

		// Note: using the actual factory here makes this an integration test!
		// FIXME: we want to inject an ExternalIdentifierRdfBuilder here somehow!
		$valueBuilderFactory = WikibaseRepo::getDefaultInstance()->getValueSnakRdfBuilderFactory();
		$entityRdfBuilderFactory = new EntityRdfBuilderFactory( $this->getRdfBuilderFactoryCallbacks( $siteLookup ), [] );
		$emitter = new NTriplesRdfWriter();
		$builder = new RdfBuilder(
			$vocabulary ?: $this->getTestData()->getVocabulary(),
			$valueBuilderFactory,
			$this->getTestData()->getMockRepository(),
			$entityRdfBuilderFactory,
			$produce,
			$emitter,
			$dedup,
			$entityContentFactory
		);

		$builder->startDocument();
		return $builder;
	}

	/**
	 * Load entity from JSON
	 *
	 * @param string $idString
	 *
	 * @return EntityDocument
	 */
	public function getEntityData( $idString ) {
		return $this->getTestData()->getEntity( $idString );
	}

	public function provideAddEntity() {
		$rdfTests = [
			[ 'Q1', 'Q1_info' ],
			[ 'Q2', [ 'Q2_meta', 'Q2_version', 'Q2_stub', 'Q2_aliases' ] ],
			[ 'Q3', [ 'Q3_meta', 'Q3_version', 'Q3_sitelinks' ] ],
			[
				'Q4_no_prefixed_ids',
				[
					'Q4_meta',
					'Q4_version',
					'Q4_statements_foreignsource_properties',
					'Q4_direct_foreignsource_properties',
					'Q4_values_foreignsource_properties'
				]
			],
			[ 'Q5', 'Q5_badges' ],
			[
				'Q6_no_prefixed_ids',
				[
					'Q6_meta',
					'Q6_version',
					'Q6_statements_foreignsource_properties',
					'Q6_qualifiers_foreignsource_properties',
					'Q6_values_foreignsource_properties',
					'Q6_referenced_foreignsource_properties'
				]
			],
			[
				'Q7_no_prefixed_ids',
				[
					'Q7_meta',
					'Q7_version',
					'Q7_statements_foreignsource_properties',
					'Q7_reference_refs_foreignsource_properties',
					'Q7_references_foreignsource_properties',
					'Q7_values_foreignsource_properties'
				]
			],
			[ 'Q8', 'Q8_baddates_foreignsource_properties' ],
		];

		return $rdfTests;
	}

	/**
	 * @dataProvider provideAddEntity
	 */
	public function testAddEntity( $entityName, $dataSetNames ) {
		$entity = $this->getEntityData( $entityName );

		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL );
		$builder->addEntity( $entity );
		$builder->addEntityRevisionInfo( $entity->getId(), 42, "2013-10-04T03:31:05Z" );

		$this->helper->assertNTriplesEqualsDataset( $dataSetNames, $builder->getRDF() );
	}

	public function testAddEntityStub() {
		$entity = $this->getEntityData( 'Q2' );

		$builder = $this->newRdfBuilder(
			RdfProducer::PRODUCE_ALL_STATEMENTS |
			RdfProducer::PRODUCE_TRUTHY_STATEMENTS |
			RdfProducer::PRODUCE_QUALIFIERS |
			RdfProducer::PRODUCE_REFERENCES |
			RdfProducer::PRODUCE_SITELINKS |
			RdfProducer::PRODUCE_VERSION_INFO |
			RdfProducer::PRODUCE_FULL_VALUES
		);
		$builder->addEntityStub( $entity );

		$this->helper->assertNTriplesEqualsDataset( [ 'Q2_stub' ], $builder->getRDF() );
	}

	public function testAddSubEntity() {
		$mainEntity = $this->getEntityData( 'Q2' );
		$subEntity = $this->getEntityData( 'Q3' );

		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL );
		$builder->subEntityMentioned( $subEntity );
		$builder->addEntity( $mainEntity );
		$builder->addEntityRevisionInfo( $mainEntity->getId(), 42, "2013-10-04T03:31:05Z" );
		$builder->addEntityRevisionInfo( $subEntity->getId(), 42, "2013-10-04T03:31:05Z" );

		$this->helper->assertNTriplesEqualsDataset(
			[ 'Q2_meta', 'Q2_version', 'Q2_stub', 'Q2_aliases', 'Q3_meta', 'Q3_version', 'Q3_sitelinks' ],
			$builder->getRDF()
		);
	}

	public function testAddEntityRedirect() {
		$builder = self::newRdfBuilder( 0 );

		$q1 = new ItemId( 'Q1' );
		$q11 = new ItemId( 'Q11' );
		$builder->addEntityRedirect( $q11, $q1 );

		$expected =
			'<http://acme.test/Q11> <http://www.w3.org/2002/07/owl#sameAs> <http://acme.test/Q1> .';
		$this->helper->assertNTriplesEquals( $expected, $builder->getRDF() );
	}

	public function getProduceOptions() {
		return [
			[
				'Q4_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS,
				[ 'Q4_meta', 'Q4_statements_foreignsource_properties' ]
			],
			[
				'Q4_no_prefixed_ids',
				RdfProducer::PRODUCE_TRUTHY_STATEMENTS,
				[ 'Q4_meta', 'Q4_direct_foreignsource_properties' ]
			],
			[
				'Q6_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS,
				[ 'Q6_meta', 'Q6_statements_foreignsource_properties' ]
			],
			[
				'Q6_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS | RdfProducer::PRODUCE_QUALIFIERS,
				[ 'Q6_meta', 'Q6_statements_foreignsource_properties', 'Q6_qualifiers_foreignsource_properties' ]
			],
			[
				'Q7_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS,
				[ 'Q7_meta', 'Q7_statements_foreignsource_properties' ]
			],
			[
				'Q7_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS | RdfProducer::PRODUCE_REFERENCES,
				[
					'Q7_meta',
					'Q7_statements_foreignsource_properties',
					'Q7_reference_refs_foreignsource_properties',
					'Q7_references_foreignsource_properties'
				]
			],
			[
				'Q3',
				RdfProducer::PRODUCE_SITELINKS,
				[ 'Q3_meta', 'Q3_sitelinks' ]
			],
			[
				'Q4_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS | RdfProducer::PRODUCE_PROPERTIES,
				[ 'Q4_meta', 'Q4_statements_foreignsource_properties', 'Q4_props_foreignsource_properties' ]
			],
			[
				'Q4_no_prefixed_ids',
				RdfProducer::PRODUCE_ALL_STATEMENTS | RdfProducer::PRODUCE_FULL_VALUES,
				[ 'Q4_meta', 'Q4_values_foreignsource_properties', 'Q4_statements_foreignsource_properties' ]
			],
			[
				'Q1',
				RdfProducer::PRODUCE_VERSION_INFO,
				'Q1_info'
			],
			[
				'Q4_no_prefixed_ids',
				RdfProducer::PRODUCE_TRUTHY_STATEMENTS | RdfProducer::PRODUCE_RESOLVED_ENTITIES,
				[ 'Q4_meta', 'Q4_direct_foreignsource_properties', 'Q4_referenced' ]
			],
			[
				'Q10',
				RdfProducer::PRODUCE_TRUTHY_STATEMENTS | RdfProducer::PRODUCE_RESOLVED_ENTITIES,
				'Q10_redirect_foreignsource_properties'
			],
		];
	}

	/**
	 * @dataProvider getProduceOptions
	 */
	public function testRdfOptions( $entityName, $produceOption, $dataSetNames ) {
		$entity = $this->getEntityData( $entityName );

		$builder = $this->newRdfBuilder( $produceOption );
		$builder->addEntity( $entity );
		$builder->addEntityRevisionInfo( $entity->getId(), 42, "2013-10-04T03:31:05Z" );
		$builder->resolveMentionedEntities( $this->getTestData()->getMockRepository() );
		$this->helper->assertNTriplesEqualsDataset( $dataSetNames, $builder->getRDF() );
	}

	public function testDumpHeader() {
		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_VERSION_INFO );
		$builder->addDumpHeader( 1426110695 );
		$dataSetNames = 'dumpheader';
		$this->helper->assertNTriplesEqualsDataset( $dataSetNames, $builder->getRDF() );
	}

	public function testDeduplication() {
		$bag = new HashDedupeBag();

		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL, $bag );
		$builder->addEntity( $this->getEntityData( 'Q7_no_prefixed_ids' ) );
		$data1 = $builder->getRDF();

		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL, $bag );
		$builder->addEntity( $this->getEntityData( 'Q9_no_prefixed_ids' ) );
		$data2 = $builder->getRDF();

		$this->helper->assertNTriplesEqualsDataset( 'Q7_Q9_dedup_foreignsource_properties', $data1 . $data2 );
	}

	public function getProps() {
		return [
			'simple prop' => [
				'prop1',
				[
					'claims' => [ 'name' => 'rdf-claims' ]
				]
			],
			'two props' => [
				'prop2',
				[
					'claims' => [ 'name' => 'rdf-claims' ],
					'sitelinks' => [ 'name' => 'rdf-sitelinks' ]
				]
			],
			'unknown prop' => [
				'prop3',
				[
					'Xclaims' => [ 'name' => 'rdf-claims' ],
					'sitelinks' => [ 'name' => 'rdf-sitelinks' ]
				]
			],
			'types' => [
				'prop4',
				[
					'claims' => [ 'name' => 'rdf-claims', 'type' => 'integer' ],
					'sitelinks' => [ 'name' => 'rdf-sitelinks', 'type' => 'float' ]
				]
			],
		];
	}

	private function getContentFactoryMock(): EntityContentFactory {
		$contentFactoryMock = $this->createMock( EntityContentFactory::class );
		$contentFactoryMock->method( 'newFromEntity' )
			->willReturnCallback( function ( EntityDocument $entity ): EntityContent {
				$contentMock = $this->createMock( EntityContent::class );
				$contentMock->method( 'getEntityPageProperties' )
					->willReturn( [
						'claims' => 'testclaims',
						'lenclaims' => strlen( 'claims' ),
						'sitelinks' => 'testsitelinks',
						'lensitelinks' => strlen( 'sitelinks' ),
					] );
				return $contentMock;
			} );
		return $contentFactoryMock;
	}

	/**
	 * @dataProvider getProps
	 * @param string $name Datafile name
	 * @param array $props Property config
	 */
	public function testPageProps( $name, $props ) {
		$vocab = new RdfVocabulary(
			[ '' => RdfBuilderTestData::URI_BASE ],
			[ '' => RdfBuilderTestData::URI_DATA ],
			new EntitySourceDefinitions( [
				new EntitySource( '', 'somedb', [ 'item' => [ 'namespaceId' => 123, 'slot' => 'main' ] ], '', '', '', '' )
			], new EntityTypeDefinitions( [] ) ),
			'',
			[ '' => '' ],
			[ '' => '' ],
			[],
			[],
			$props,
			'http://creativecommons.org/publicdomain/zero/1.0/'
		);
		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL, null, $vocab );

		TestingAccessWrapper::newFromObject( $builder )->entityContentFactory
			= $this->getContentFactoryMock();

		$builder->addEntityPageProps( $this->getEntityData( 'Q9' ) );
		$data = $builder->getRDF();

		$this->helper->assertNTriplesEqualsDataset( $name, $data );
	}

	public function testPagePropsNone() {
		// Props disabled by flag
		$props = [
			'claims' => [ 'name' => 'rdf-claims' ]
		];
		$vocab = new RdfVocabulary(
			[ '' => RdfBuilderTestData::URI_BASE ],
			[ '' => RdfBuilderTestData::URI_DATA ],
			new EntitySourceDefinitions( [], new EntityTypeDefinitions( [] ) ),
			'',
			[ '' => '' ],
			[ '' => '' ],
			[],
			[],
			$props,
			'http://creativecommons.org/publicdomain/zero/1.0/'
		);
		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL & ~RdfProducer::PRODUCE_PAGE_PROPS, null, $vocab );

		$builder->addEntityPageProps( $this->getEntityData( 'Q9' ) );
		$data = $builder->getRDF();
		$this->assertSame( "", $data, "Should return empty string" );

		// Props disabled by config of vocabulary
		$builder = $this->newRdfBuilder( RdfProducer::PRODUCE_ALL );

		$builder->addEntityPageProps( $this->getEntityData( 'Q9' ) );
		$data = $builder->getRDF();
		$this->assertSame( "", $data, "Should return empty string" );
	}

}

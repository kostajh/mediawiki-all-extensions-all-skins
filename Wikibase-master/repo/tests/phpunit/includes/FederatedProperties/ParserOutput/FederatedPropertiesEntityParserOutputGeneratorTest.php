<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Tests\FederatedProperties\ParserOutput;

use Language;
use Psr\SimpleCache\CacheInterface;
use RepoGroup;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Entity\PropertyDataTypeMatcher;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\Lib\Store\EntityRevision;
use Wikibase\Repo\FederatedProperties\ApiEntityLookup;
use Wikibase\Repo\FederatedProperties\ApiRequestExecutionException;
use Wikibase\Repo\FederatedProperties\FederatedPropertiesEntityParserOutputGenerator;
use Wikibase\Repo\FederatedProperties\FederatedPropertiesError;
use Wikibase\Repo\LinkedData\EntityDataFormatProvider;
use Wikibase\Repo\ParserOutput\CompositeStatementDataUpdater;
use Wikibase\Repo\ParserOutput\ExternalLinksDataUpdater;
use Wikibase\Repo\ParserOutput\FullEntityParserOutputGenerator;
use Wikibase\Repo\ParserOutput\ImageLinksDataUpdater;
use Wikibase\Repo\ParserOutput\ItemParserOutputUpdater;
use Wikibase\Repo\ParserOutput\ReferencedEntitiesDataUpdater;
use Wikibase\Repo\Tests\ParserOutput\EntityParserOutputGeneratorTestBase;
use Wikibase\View\LocalizedTextProvider;
use Wikibase\View\Template\TemplateFactory;

/**
 * @covers \Wikibase\Repo\FederatedProperties\FederatedPropertiesEntityParserOutputGenerator
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class FederatedPropertiesEntityParserOutputGeneratorTest extends EntityParserOutputGeneratorTestBase {

	public function testShouldPrefetchFederatedProperties() {
		$labelLanguage = 'en';
		$userLanguage = 'en';

		$item = new Item( new ItemId( 'Q7799929' ) );
		$item->setLabel( $labelLanguage, 'kitten item' );

		$statementWithReference = new Statement( new PropertyNoValueSnak( 1 ) );
		$statementWithReference->addNewReference( new PropertyNoValueSnak( 4 ) );

		$item->getStatements()->addStatement( $statementWithReference );
		$item->getStatements()->addStatement( new Statement( new PropertyNoValueSnak( 2 ) ) );
		$item->getStatements()->addStatement( new Statement( new PropertyNoValueSnak( 3 ) ) );
		$item->getStatements()->addStatement( new Statement( new PropertyNoValueSnak( 3 ) ) );

		$expectedIds = [
			new PropertyId( "P1" ),
			new PropertyId( "P2" ),
			new PropertyId( "P3" ),
			new PropertyId( "P4" ),
		];

		$this->entityViewFactory = $this->mockEntityViewFactory( false );

		$prefetchingTermLookup = $this->createMock( ApiEntityLookup::class );
		$prefetchingTermLookup->expects( $this->once() )
			->method( 'fetchEntities' )
			->willReturnCallback( $this->getPrefetchTermsCallback(
				$expectedIds
			) );

		$innerPog = $this->getFullGeneratorMock();
		$entityParserOutputGenerator = $this->newEntityParserOutputGenerator( $prefetchingTermLookup, $innerPog, $userLanguage );

		$entityParserOutputGenerator->getParserOutput( new EntityRevision( $item, 4711 ), false );
	}

	public function testShouldNotCallPrefetchIfNoProperties() {
		$labelLanguage = 'en';
		$userLanguage = 'en';

		$item = new Item( new ItemId( 'Q7799929' ) );
		$item->setLabel( $labelLanguage, 'kitten item' );

		$prefetchingTermLookup = $this->createMock( ApiEntityLookup::class );
		$prefetchingTermLookup->expects( $this->never() )
			->method( 'fetchEntities' );

		$this->entityViewFactory = $this->mockEntityViewFactory( false );

		$innerPog = $this->getFullGeneratorMock();
		$entityParserOutputGenerator = $this->newEntityParserOutputGenerator( $prefetchingTermLookup, $innerPog, $userLanguage );

		$entityParserOutputGenerator->getParserOutput( new EntityRevision( $item, 4711 ), false );
	}

	/**
	 * @dataProvider errorPageProvider
	 */
	public function testGetParserOutputHandlesFederatedApiException( $labelLanguage, $userLanguage ) {

		$item = new Item( new ItemId( 'Q7799929' ) );
		$item->setLabel( $labelLanguage, 'kitten item' );

		$prefetchingTermLookup = $this->createMock( ApiEntityLookup::class );
		$prefetchingTermLookup->expects( $this->never() )
			->method( 'fetchEntities' );

		$updater = $this->createMock( ItemParserOutputUpdater::class );

		$this->entityViewFactory = $this->mockEntityViewFactory( false );

		$entityParserOutputGenerator = $this->newEntityParserOutputGenerator(
			$prefetchingTermLookup,
			$this->getFullGeneratorMock( [ $updater ], $userLanguage ),
			$userLanguage
		);
		$updater->method( 'updateParserOutput' )
			->willThrowException( new ApiRequestExecutionException() );

		// T254888 Exception will be handled and show an error page.
		$this->expectException( FederatedPropertiesError::class );

		$entityParserOutputGenerator->getParserOutput( new EntityRevision( $item, 4711 ), false );
	}

	public function testParserOutputLoadModule() {
		$item = new Item( new ItemId( 'Q7799929' ) );
		$item->setLabel( 'en', 'kitten item' );
		$entityRevision = new EntityRevision( $item, 4711 );

		$prefetchingTermLookup = $this->createMock( ApiEntityLookup::class );
		$prefetchingTermLookup->expects( $this->never() )
			->method( 'fetchEntities' );

		$updater = $this->createMock( ItemParserOutputUpdater::class );

		$this->entityViewFactory = $this->mockEntityViewFactory( true );

		$parserOutputGen = $this->newEntityParserOutputGenerator(
			$prefetchingTermLookup,
			$this->getFullGeneratorMock( [ $updater ], 'en' ),
			'en'
		);

		$parserOutput = $parserOutputGen->getParserOutput( $entityRevision );
		$resourceLoaderModules = $parserOutput->getModules();
		$this->assertContains( 'wikibase.federatedPropertiesLeavingSiteNotice', $resourceLoaderModules );
		$this->assertContains( 'wikibase.federatedPropertiesEditRequestFailureNotice', $resourceLoaderModules );
	}

	public function errorPageProvider() {
		return [
			[ 'en', 'en' ],
			[ 'de', 'en' ],
		];
	}

	protected function getPrefetchTermsCallback( $expectedIds ) {
		$prefetchTerms = function (
			array $entityIds,
			array $termTypes = null,
			array $languageCodes = null
		) use (
			$expectedIds
		) {
			$expectedIdStrings = array_map( function( EntityId $id ) {
				return $id->getSerialization();
			}, $expectedIds );
			$entityIdStrings = array_map( function( EntityId $id ) {
				return $id->getSerialization();
			}, $entityIds );

			sort( $expectedIdStrings );
			sort( $entityIdStrings );

			$this->assertEquals( $expectedIdStrings, $entityIdStrings );
		};
		return $prefetchTerms;
	}

	private function newEntityParserOutputGenerator( $prefetchingTermLookup, $fullGenerator, $languageCode = 'en' ) {
		return new FederatedPropertiesEntityParserOutputGenerator(
			$fullGenerator,
			Language::factory( $languageCode ),
			$prefetchingTermLookup
		);
	}

	private function getFullGeneratorMock( $dataUpdaters = null, $language = 'en', $title = null, $description = null ) {
		$entityDataFormatProvider = new EntityDataFormatProvider();
		$entityDataFormatProvider->setAllowedFormats( [ 'json', 'ntriples' ] );

		$entityTitleLookup = $this->getEntityTitleLookupMock();

		$propertyDataTypeMatcher = new PropertyDataTypeMatcher( $this->getPropertyDataTypeLookup() );
		$repoGroup = $this->createMock( RepoGroup::class );

		$statementUpdater = new CompositeStatementDataUpdater(
			new ExternalLinksDataUpdater( $propertyDataTypeMatcher ),
			new ImageLinksDataUpdater( $propertyDataTypeMatcher, $repoGroup )
		);

		if ( $dataUpdaters === null ) {
			$dataUpdaters = [
				new ItemParserOutputUpdater( $statementUpdater ),
				new ReferencedEntitiesDataUpdater(
					$this->newEntityReferenceExtractor(),
					$entityTitleLookup
				)
			];
		}

		$cache = $this->createMock( CacheInterface::class );
		$cache->method( 'get' )
			->willReturn( false );

		return new FullEntityParserOutputGenerator(
			$this->entityViewFactory,
			$this->getEntityMetaTagsFactory( $title, $description ),
			$this->getConfigBuilderMock(),
			$entityTitleLookup,
			$this->newLanguageFallbackChain(),
			TemplateFactory::getDefaultInstance(),
			$this->createMock( LocalizedTextProvider::class ),
			$entityDataFormatProvider,
			$dataUpdaters,
			Language::factory( $language )
		);
	}
}

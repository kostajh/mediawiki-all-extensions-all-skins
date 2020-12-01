<?php

namespace WikibaseManifest\Test;

use HashConfig;
use MediaWiki\Extension\WikibaseManifest\ConceptNamespaces;
use MediaWiki\Extension\WikibaseManifest\EntityNamespaces;
use MediaWiki\Extension\WikibaseManifest\EntityNamespacesFactory;
use MediaWiki\Extension\WikibaseManifest\EquivEntities;
use MediaWiki\Extension\WikibaseManifest\EquivEntitiesFactory;
use MediaWiki\Extension\WikibaseManifest\ExternalServices;
use MediaWiki\Extension\WikibaseManifest\ExternalServicesFactory;
use MediaWiki\Extension\WikibaseManifest\ManifestGenerator;
use MediaWiki\Extension\WikibaseManifest\MaxLag;
use MediaWiki\Extension\WikibaseManifest\MaxLagFactory;
use MediaWiki\Extension\WikibaseManifest\OAuthUrl;
use MediaWiki\Extension\WikibaseManifest\OAuthUrlFactory;
use MediaWiki\Extension\WikibaseManifest\TitleFactoryMainPageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseManifest\ManifestGenerator
 */
class ManifestGeneratorTest extends TestCase {

	public function testGenerate() {
		$siteString = 'manifestsite';
		$serverString = 'http://cat/dog';
		$mainPageUrlString = $serverString . '/MainPage';
		$apiAction = '/api.php';
		$apiRest = '/rest.php';
		$scriptString = '/wikipath';
		$equivEntities = [
			'properties' => [ 'P1' => 'P2' ],
			'items' => [ 'Q42' => 'Q1' ]
		];
		$maxLag = 5;
		$mockConfig = new HashConfig(
			[
			'Server' => $serverString,
			'Sitename' => $siteString,
			'ScriptPath' => $scriptString,
			'WbManifestMaxLag' => $maxLag,
			]
		);

		$mockMainPageUrl = $this->createMock( TitleFactoryMainPageUrl::class );
		$mockMainPageUrl->expects( $this->once() )
			->method( 'getValue' )
			->willReturn( $mainPageUrlString );

		$mockEquivEntitiesFactory = $this->createMock( EquivEntitiesFactory::class );
		$mockEquivEntitiesFactory->expects( $this->once() )
			->method( 'getEquivEntities' )
			->willReturn( new EquivEntities( $equivEntities ) );

		$mockConceptNamespaces = $this->createMock( ConceptNamespaces::class );
		$mockConceptNamespaces->expects( $this->once() )
			->method( 'getLocal' )
			->willReturn( [ 'a' => 'bb' ] );

		$mockExternalServicesFactory = $this->createMock( ExternalServicesFactory::class );
		$externalServicesMappings = [ 'queryservice' => 'http://services.something' ];
		$mockExternalServicesFactory->expects( $this->once() )
			->method( 'getExternalServices' )
			->willReturn( new ExternalServices( $externalServicesMappings ) );

		$entityNamespaceMapping = [ 'item' => [ 'namespace_id' => 123, 'namespace_name' => 'Cat' ] ];
		$mockEntityNamespacesFactory = $this->createMock( EntityNamespacesFactory::class );
		$mockEntityNamespacesFactory->expects( $this->atLeastOnce() )
			->method( 'getEntityNamespaces' )
			->willReturn( new EntityNamespaces( $entityNamespaceMapping ) );

		$mockMaxLagFactory = $this->createMock( MaxLagFactory::class );
		$mockMaxLagFactory->expects( $this->atLeastOnce() )
			->method( 'getMaxLag' )
			->willReturn( new MaxLag( $maxLag ) );

		$generator = new ManifestGenerator(
			$mockConfig,
			$mockMainPageUrl,
			$mockEquivEntitiesFactory,
			$mockConceptNamespaces,
			$mockExternalServicesFactory,
			$mockEntityNamespacesFactory,
			$mockMaxLagFactory,
			$this->getMockOAuthUrlFactory()
		);
		$result = $generator->generate();

		$expectedSubset = [
			'name' => $siteString,
			'root_script_url' => $serverString . $scriptString,
			'main_page_url' => $mainPageUrlString,
			'api' => [
				'action' => $serverString . $scriptString . $apiAction,
				'rest' => $serverString . $scriptString . $apiRest
			],
			'equiv_entities' => [
				'wikidata.org' => $equivEntities,
			],
			'local_rdf_namespaces' => [ 'a' => 'bb' ],
			'external_services' => $externalServicesMappings,
			'local_entities' => $entityNamespaceMapping,
			'max_lag' => $maxLag
		];

		foreach ( $expectedSubset as $key => $value ) {
			$this->assertArrayHasKey( $key, $result );
			$this->assertSame( $value, $result[$key] );
		}
	}

	private function getMockOAuthUrlFactory(): OAuthUrlFactory {
		$OAuthUrlFactory = $this->createMock( OAuthUrlFactory::class );
		$OAuthUrlFactory
			->method( 'getOAuthUrl' )
			->willReturn( $this->createMock( OAuthUrl::class ) );
		return $OAuthUrlFactory;
	}
}

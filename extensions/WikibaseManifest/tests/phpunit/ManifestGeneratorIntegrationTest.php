<?php

namespace WikibaseManifest\Test;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;

class ManifestGeneratorIntegrationTest extends MediaWikiIntegrationTestCase {

	private const NAME = 'name';
	private const ROOT_SCRIPT_URL = 'root_script_url';
	private const MAIN_PAGE = 'main_page_url';
	private const API = 'api';
	private const EQUIV_ENTITIES = 'equiv_entities';
	private const LOCAL_RDF_NAMESPACES = 'local_rdf_namespaces';
	private const EXTERNAL_SERVICES = 'external_services';
	private const ENTITY_SOURCES = 'local_entities';
	private const MAX_LAG = 'max_lag';

	public function testGenerate() {
		$siteString = 'manifestsite';
		$serverString = 'http://cat/dog';
		$scriptString = '/wikipath';
		$rootScriptUrlString = $serverString . $scriptString;
		$mainPageUrlString = $serverString . $scriptString . '/index.php/Main_Page';
		$actionApiUrlString = $serverString . $scriptString . '/api.php';
		$restApiUrlString = $serverString . $scriptString . '/rest.php';
		$equivEntities = [
			'properties' => [ 'P1' => 'P2' ],
			'items' => [ 'Q42' => 'Q1' ]
		];
		$externalServices = [ 'quickstatements' => 'http://quickstatements.net' ];
		$maxLag = 23;
		$this->setMwGlobals(
			[
			'wgServer' => $serverString,
			'wgSitename' => $siteString,
			'wgScriptPath' => $scriptString,
			'wgArticlePath' => $serverString . $scriptString . '/index.php/$1',
			'wgWbManifestWikidataEntityMapping' => $equivEntities,
			'wgWbManifestExternalServiceMapping' => $externalServices,
			'wgWbManifestMaxLag' => $maxLag
			]
		);
		$this->mergeMwGlobalArrayValue( 'wgWBRepoSettings', [ 'sparqlEndpoint' => null ] );
		$generator = MediaWikiServices::getInstance()->getService( 'WikibaseManifestGenerator' );
		$result = $generator->generate();

		$this->assertEquals( 10, count( $result ) );

		$this->assertArrayHasKey( self::NAME, $result );
		$this->assertIsString( $result[self::NAME] );
		$this->assertEquals( $siteString, $result[self::NAME] );

		$this->assertArrayHasKey( self::ROOT_SCRIPT_URL, $result );
		$this->assertIsString( $result[self::ROOT_SCRIPT_URL] );
		$this->assertEquals( $rootScriptUrlString, $result[self::ROOT_SCRIPT_URL] );

		$this->assertArrayHasKey( self::MAIN_PAGE, $result );
		$this->assertIsString( $result[self::MAIN_PAGE] );
		$this->assertEquals( $mainPageUrlString, $result[self::MAIN_PAGE] );

		$this->assertArrayHasKey( self::API, $result );
		$this->assertIsArray( $result[self::API] );
		$this->assertArrayHasKey( 'action', $result[self::API] );
		$this->assertIsString( $result[self::API]['action'] );
		$this->assertEquals( $actionApiUrlString, $result[self::API]['action'] );
		$this->assertArrayHasKey( 'rest', $result[self::API] );
		$this->assertIsString( $result[self::API]['rest'] );
		$this->assertEquals( $restApiUrlString, $result[self::API]['rest'] );

		$this->assertArrayHasKey( self::EQUIV_ENTITIES, $result );
		$this->assertIsArray( $result[self::EQUIV_ENTITIES] );
		$this->assertArrayHasKey( 'wikidata.org', $result[self::EQUIV_ENTITIES] );
		$this->assertIsArray( $result[self::EQUIV_ENTITIES]['wikidata.org'] );
		$this->assertArrayEquals( $equivEntities, $result[self::EQUIV_ENTITIES]['wikidata.org'] );

		$this->assertArrayHasKey( self::EXTERNAL_SERVICES, $result );
		$this->assertIsArray( $result[self::EXTERNAL_SERVICES] );
		$this->assertArrayEquals( $externalServices, $result[self::EXTERNAL_SERVICES] );

		$this->assertArrayHasKey( self::LOCAL_RDF_NAMESPACES, $result );
		$this->assertIsArray( $result[self::LOCAL_RDF_NAMESPACES] );
		$rdfKeys = [
			"",
			"data",
			"s",
			"ref",
			"v",
			"t",
			"tn",
			"p",
			"ps",
			"psv",
			"psn",
			"pq",
			"pqv",
			"pqn",
			"pr",
			"prv",
			"prn",
			"no",
		];
		foreach ( $rdfKeys as $key ) {
			$this->assertArrayHasKey( $key, $result[self::LOCAL_RDF_NAMESPACES] );
		}

		$this->assertArrayHasKey( self::ENTITY_SOURCES, $result );
		$this->assertIsArray( $result[self::ENTITY_SOURCES] );
		$this->assertArrayHasKey( 'item', $result[self::ENTITY_SOURCES] );
		$this->assertArrayHasKey( 'property', $result[self::ENTITY_SOURCES] );

		$this->assertArrayHasKey( self::MAX_LAG, $result );
		$this->assertIsInt( $result[self::MAX_LAG] );
		$this->assertEquals( $maxLag, $result[self::MAX_LAG] );

		$this->assertArrayHasKey( 'registration_page', $result['oauth'] );
	}

	public function testMaxLagDefaultValue() {
		// create generator without pre-configured 'wgWbManifestMaxLag' value
		$generator = MediaWikiServices::getInstance()->getService( 'WikibaseManifestGenerator' );
		$result = $generator->generate();

		// expect default value
		$this->assertArrayHasKey( self::MAX_LAG, $result );
		$this->assertIsInt( $result[self::MAX_LAG] );
		$this->assertEquals( 5, $result[self::MAX_LAG] );
	}
}

<?php
namespace MultiMaps;

class MapServicesTest extends \MediaWikiTestCase {

	/**
	 *
	 */
	public function testGetServiceInstance() {
		global $egMultiMaps_MapServices;

		$defaultService = MapServices::getServiceInstance();
		$firstService = MapServices::getServiceInstance( $egMultiMaps_MapServices[0] );
		$defaultServiceWARNING = MapServices::getServiceInstance( 'fictive' );
		$this->assertTrue( $defaultService instanceof BaseMapService );
		$this->assertTrue( $firstService instanceof BaseMapService );
		$this->assertTrue( $defaultServiceWARNING instanceof BaseMapService );
		$this->assertEquals( $defaultService, $firstService );
		$this->assertEquals( $defaultService->classname, $defaultServiceWARNING->classname );
		$this->assertEquals( $defaultService->getErrorMessages(), [] );
		$this->assertNotEquals( $defaultServiceWARNING->getErrorMessages(), [] );
	}

	public function testExceptionOnGetServiceInstance() {
		global $egMultiMaps_MapServices;

		$MapServices = $egMultiMaps_MapServices;
		$egMultiMaps_MapServices = [];

		$this->assertFalse( MapServices::getServiceInstance() instanceof BaseMapService );

		$egMultiMaps_MapServices = $MapServices;
	}

	public function testErrorsOnGetServiceInstance() {
		global $egMultiMaps_MapServices;

		$MapServices = $egMultiMaps_MapServices;
		$egMultiMaps_MapServices = [ 'fictive', 'Marker' ];

		$this->assertFalse( MapServices::getServiceInstance( 'fictive' ) instanceof BaseMapService );
		$this->assertFalse( MapServices::getServiceInstance( 'Marker' ) instanceof BaseMapService );
		$this->assertFalse( MapServices::getServiceInstance( 'fictivefictive' ) instanceof BaseMapService );

		$egMultiMaps_MapServices = $MapServices;
	}

}

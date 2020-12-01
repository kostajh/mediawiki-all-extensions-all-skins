<?php

namespace WikibaseManifest\Test;

use HashConfig;
use MediaWiki\Extension\WikibaseManifest\ConfigMaxLagFactory;
use MediaWiki\Extension\WikibaseManifest\MaxLag;
use MediaWiki\Extension\WikibaseManifest\WbManifest;
use PHPUnit\Framework\TestCase;

class ConfigMaxLagFactoryTest extends TestCase {

	public function test() {
		$configValueName = WbManifest::MAX_LAG_CONFIG;
		$mockConfig = new HashConfig( [ 'WbManifestMaxLag' => 23 ] );

		$factory = new ConfigMaxLagFactory(
			$mockConfig, $configValueName
		);
		$this->assertEquals( new MaxLag( 23 ), $factory->getMaxLag() );
	}

}

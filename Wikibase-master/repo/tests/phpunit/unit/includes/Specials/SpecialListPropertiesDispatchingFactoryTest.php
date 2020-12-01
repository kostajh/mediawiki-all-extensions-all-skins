<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wikibase\Lib\DataTypeFactory;
use Wikibase\Repo\FederatedProperties\SpecialListFederatedProperties;
use Wikibase\Repo\Specials\SpecialListProperties;
use Wikibase\Repo\Specials\SpecialListPropertiesDispatchingFactory;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\Specials\SpecialListPropertiesDispatchingFactory
 *
 * @group SpecialPage
 * @group Wikibase
 * @group WikibaseSpecialPage
 *
 * @license GPL-2.0-or-later
 */
class SpecialListPropertiesDispatchingFactoryTest extends TestCase {
	public function testFactoryNoFederation() {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		$wikibaseRepo->getSettings()->setSetting( 'federatedPropertiesEnabled', true );

		$specialPage = SpecialListPropertiesDispatchingFactory::factory(
			new DataTypeFactory( [] )
		);

		$this->assertInstanceOf( SpecialListFederatedProperties::class, $specialPage );
	}

	public function testFactoryFederation() {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		$wikibaseRepo->getSettings()->setSetting( 'federatedPropertiesEnabled', false );

		$specialPage = SpecialListPropertiesDispatchingFactory::factory(
			new DataTypeFactory( [] )
		);

		$this->assertInstanceOf( SpecialListProperties::class, $specialPage );
	}
}

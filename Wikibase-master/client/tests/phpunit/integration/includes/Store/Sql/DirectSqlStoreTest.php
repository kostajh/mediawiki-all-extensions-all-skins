<?php

namespace Wikibase\Client\Tests\Integration\Store\Sql;

use MediaWikiIntegrationTestCase;
use Wikibase\Client\RecentChanges\RecentChangesFinder;
use Wikibase\Client\Store\Sql\DirectSqlStore;
use Wikibase\Client\Usage\ImplicitDescriptionUsageLookup;
use Wikibase\Client\Usage\SubscriptionManager;
use Wikibase\Client\Usage\UsageLookup;
use Wikibase\Client\Usage\UsageTracker;
use Wikibase\Client\WikibaseClient;
use Wikibase\DataAccess\NullPrefetchingTermLookup;
use Wikibase\DataAccess\WikibaseServices;
use Wikibase\DataModel\Entity\ItemIdParser;
use Wikibase\DataModel\Services\Entity\EntityPrefetcher;
use Wikibase\DataModel\Services\Entity\NullEntityPrefetcher;
use Wikibase\DataModel\Services\EntityId\EntityIdComposer;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\Lib\Changes\EntityChangeFactory;
use Wikibase\Lib\Store\EntityIdLookup;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Lib\Store\PropertyInfoLookup;
use Wikibase\Lib\Store\SiteLinkLookup;
use Wikibase\Lib\Store\Sql\EntityChangeLookup;
use Wikibase\Lib\Tests\Store\MockPropertyInfoLookup;
use Wikibase\Lib\WikibaseSettings;

/**
 * @covers \Wikibase\Client\Store\Sql\DirectSqlStore
 *
 * @group Database
 * @group Wikibase
 * @group WikibaseClient
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DirectSqlStoreTest extends MediaWikiIntegrationTestCase {

	protected function newStore() {
		$entityChangeFactory = $this->getMockBuilder( EntityChangeFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$wikibaseClient = WikibaseClient::getDefaultInstance( 'reset' );

		$wikibaseServices = $this->createMock( WikibaseServices::class );

		$wikibaseServices->method( 'getEntityPrefetcher' )
			->willReturn( new NullEntityPrefetcher() );
		$wikibaseServices->method( 'getEntityRevisionLookup' )
			->willReturn( $this->createMock( EntityRevisionLookup::class ) );
		$wikibaseServices->method( 'getPropertyInfoLookup' )
			->willReturn( new MockPropertyInfoLookup() );
		$wikibaseServices->method( 'getTermBuffer' )
			->willReturn( new NullPrefetchingTermLookup() );

		return new DirectSqlStore(
			$entityChangeFactory,
			new ItemIdParser(),
			new EntityIdComposer( [] ),
			$this->createMock( EntityIdLookup::class ),
			new EntityNamespaceLookup( [] ),
			$wikibaseServices,
			$wikibaseClient->getSettings(),
			wfWikiID(),
			'en'
		);
	}

	public static function tearDownAfterClass(): void {
		// ensure we don’t leave an instance with non-default settings behind
		WikibaseClient::getDefaultInstance( 'reset' );
	}

	/**
	 * @dataProvider provideGetters
	 */
	public function testGetters( $getter, $expectedType ) {
		$store = $this->newStore();

		$this->assertTrue( method_exists( $store, $getter ), "Method $getter" );

		$obj = $store->$getter();

		$this->assertInstanceOf( $expectedType, $obj );
	}

	public function provideGetters() {
		return [
			[ 'getSiteLinkLookup', SiteLinkLookup::class ],
			[ 'getEntityLookup', EntityLookup::class ],
			[ 'getPropertyInfoLookup', PropertyInfoLookup::class ],
			[ 'getUsageTracker', UsageTracker::class ],
			[ 'getUsageLookup', UsageLookup::class ],
			[ 'getEntityIdLookup', EntityIdLookup::class ],
			[ 'getEntityPrefetcher', EntityPrefetcher::class ],
			[ 'getEntityChangeLookup', EntityChangeLookup::class ],
			[ 'getRecentChangesFinder', RecentChangesFinder::class ],
		];
	}

	public function testGetSubscriptionManager() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->markTestSkipped( 'getSubscriptionManager needs the repository extension to be active.' );
		}

		$store = $this->newStore();

		$this->assertInstanceOf( SubscriptionManager::class, $store->getSubscriptionManager() );
	}

	/** @dataProvider provideBooleans */
	public function testGetUsageLookup( bool $enableImplicitDescriptionUsage ) {
		$this->mergeMwGlobalArrayValue( 'wgWBClientSettings', [
			'enableImplicitDescriptionUsage' => $enableImplicitDescriptionUsage,
		] );

		$store = $this->newStore();
		$usageLookup = $store->getUsageLookup();

		if ( $enableImplicitDescriptionUsage ) {
			$this->assertInstanceOf( ImplicitDescriptionUsageLookup::class, $usageLookup );
		} else {
			$this->assertNotInstanceOf( ImplicitDescriptionUsageLookup::class, $usageLookup );
		}
	}

	public function provideBooleans() {
		yield [ true ];
		yield [ false ];
	}

}

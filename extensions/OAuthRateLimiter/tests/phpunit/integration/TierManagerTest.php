<?php

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\OAuthRateLimiter\ClientTierStore;
use MediaWiki\Extension\OAuthRateLimiter\TierManager;
use MediaWiki\Extensions\OAuth\Entity\ClientEntity;
use MediaWiki\Extensions\OAuth\Tests\Entity\Mock_ClientEntity;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @covers \MediaWiki\Extension\OAuthRateLimiter\TierManager
 * @group Database
 */
class TierManagerTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var ILoadBalancer
	 */
	private $loadBalancer;

	protected function setUp(): void {
		$this->loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();

		$this->tablesUsed[] = 'oauth_registered_consumer';
		$this->tablesUsed[] = 'oauth_ratelimit_client_tier';
	}

	private function getClientEntity() : ClientEntity {
		$clientEntity = Mock_ClientEntity::newMock( $this->getTestUser()->getUser() );
		$db = $this->loadBalancer->getConnectionRef( DB_MASTER );
		$this->assertTrue( $clientEntity->save( $db ), 'Sanity: must create a client' );

		return $clientEntity;
	}

	private function getTierManager( $defaultClientTier, $tierConfig ) {
		return new TierManager(
			new ServiceOptions(
				TierManager::CONSTRUCTOR_OPTIONS,
				new HashConfig( [
					'OAuthRateLimiterDefaultClientTier' => $defaultClientTier,
					'OAuthRateLimiterTierConfig' => $tierConfig
				] )
			),
			LoggerFactory::getInstance( 'TierManagerTest' ),
			new ClientTierStore( $this->loadBalancer )
		);
	}

	public function provideTiers() {
		yield 'Tier exist in config' => [
			'tier 2',
			'default',
			[
				'tier 2' => [
					'ratelimit' => [
						'requests_per_unit' => 9,
						'unit' => 'minute'
					]
				],
				'default' => [
					'ratelimit' => [
						'requests_per_unit' => 9879,
						'unit' => 'minute'
					]
				]
			],
			[
				'ratelimit' => [
					'requests_per_unit' => 9,
					'unit' => 'minute'
				]
			]
		];

		yield 'Tier does not match config but default exist' => [
			'tier 2',
			'tier 28',
			[
				'tier 28' => [
					'ratelimit' => [
						'requests_per_unit' => 987,
						'unit' => 'minute'
					]
				],
				'tier 29' => [
					'ratelimit' => [
						'requests_per_unit' => 9,
						'unit' => 'minute'
					]
				]
			],
			[
				'ratelimit' => [
					'requests_per_unit' => 987,
					'unit' => 'minute'
				]
			]
		];

		yield 'Both default name and tier config are empty' => [
			'tier x',
			'',
			[],
			[]
		];
	}

	/**
	 * @dataProvider provideTiers
	 * @param string $tierName
	 * @param string $defaultTierName
	 * @param array $tierConfig
	 * @param array $expectedTierConfig
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\TierManager::getClientTierConfig
	 */
	public function testGetClientTierConfig( $tierName, $defaultTierName, $tierConfig, $expectedTierConfig ) {
		$clientID = $this->getClientEntity()->getConsumerKey();
		$tierManager = $this->getTierManager( $defaultTierName, $tierConfig );

		$this->assertTrue(
			$this->db->insert(
				'oauth_ratelimit_client_tier',
				[
					'oarct_client_id' => $clientID,
					'oarct_tier_name' => $tierName,
				],
				__METHOD__
			),
			'Sanity: must add client tier'
		);

		$clientTierConfig = $tierManager->getClientTierConfig( $clientID );
		$this->assertArrayEquals( $expectedTierConfig, $clientTierConfig );
	}

	public function provideDefaultTiers() {
		yield 'default tier name match in tier config' => [
			'tier I',
			'tier I'
		];

		yield 'Default tier empty' => [
			'',
			null
		];

		yield 'Default tier set to false' => [
			false,
			null
		];
	}

	/**
	 * @dataProvider provideDefaultTiers
	 * @param $defaultTierName
	 * @param $expectedTierName
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\TierManager::getDefaultTierName
	 */
	public function testGetDefaultTier( $defaultTierName, $expectedTierName ) {
		$tierManager = $this->getTierManager( $defaultTierName, [] );
		$tierName = $tierManager->getDefaultTierName();

		$this->assertSame( $expectedTierName, $tierName );
	}
}

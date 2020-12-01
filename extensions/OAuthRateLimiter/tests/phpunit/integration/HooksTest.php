<?php

use MediaWiki\Extensions\OAuth\Entity\ClaimEntity;
use MediaWiki\Extensions\OAuth\Repository\ClaimStore;
use MediaWiki\Extensions\OAuth\Tests\Entity\Mock_ClientEntity;
use MediaWiki\MediaWikiServices;

/**
 * @covers \MediaWiki\Extension\OAuthRateLimiter\Hooks
 * @group Database
 */
class HooksTest extends MediaWikiIntegrationTestCase {
	/**
	 * @var ClaimStore
	 */
	private $claimStore;

	protected function setUp(): void {
		$this->claimStore = new ClaimStore();
		$this->tablesUsed[] = 'oauth_registered_consumer';
		$this->tablesUsed[] = 'oauth_ratelimit_client_tier';
	}

	private function getClientEntity() {
		$clientEntity = Mock_ClientEntity::newMock( $this->getTestUser()->getUser() );
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnectionRef( DB_MASTER );
		$this->assertTrue( $clientEntity->save( $db ), 'Sanity: must create a client' );

		return $clientEntity;
	}

	public function provideDefaultClientTier() {
		yield 'empty default name' => [
			'',
			[],
			[]
		];

		yield 'default name set without match in tier config' => [
			'default',
			[
				'tier 1' => [
					'ratelimit' => [
						'requests_per_unit' => 10,
						'unit'  => 'minute'
					]
				]
			],
			[]
		];

		yield 'default name set with match in tier config' => [
			'default',
			[
				'default' => [
					'ratelimit' => [
						'requests_per_unit' => 10,
						'unit'  => 'minute'
					]
				]
			],
			[
				new ClaimEntity( 'ratelimit', [
					'requests_per_unit' => 10,
					'unit'  => 'minute'
				] )
			]
		];
	}

	/**
	 * @dataProvider provideDefaultClientTier
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\Hooks::onOAuthClaimStoreGetClaims
	 * @param $defaultName
	 * @param $tierConfig
	 * @param array $expectedClaimEntities
	 */
	public function testOnOAuthClaimStoreGetClaimsWithDefaultTier( $defaultName, $tierConfig, $expectedClaimEntities ) {
		$this->setMwGlobals( 'wgOAuthRateLimiterDefaultClientTier', $defaultName );
		$this->setMwGlobals( 'wgOAuthRateLimiterTierConfig', $tierConfig );

		$claims = $this->claimStore->getClaims( 'dummyGrant',  $this->getClientEntity() );

		$this->assertEquals( count( $expectedClaimEntities ), count( $claims ) );
		foreach ( $expectedClaimEntities as $index => $claimEntity ) {
			$this->assertEquals( $claimEntity->getName(), $claims[$index]->getName() );
			$this->assertEquals( $claimEntity->getValue(), $claims[$index]->getValue() );
		}
	}

	public function provideDatabaseTiers() {
		yield 'tier name in db and matches tier config' => [
			'tier IV',
			'tier I',
			[
				'tier I' => [
					'ratelimit' => [
						'requests_per_unit' => 10,
						'unit'  => 'minute'
					]
				],
				'tier IV' => [
					'ratelimit' => [
						'requests_per_unit' => 999,
						'unit'  => 'minute'
					]
				]
			],
			[
				new ClaimEntity( 'ratelimit',
					[
						'requests_per_unit' => 999,
						'unit'  => 'minute'
					]
				)
			]
		];

		yield 'tier name in db does not match tier config' => [
			'tier II',
			'default',
			[
				'tier III' => [
					'ratelimit' => [
						'requests_per_unit' => 999,
						'unit'  => 'minute'
					]
				],
				'default' => [
					'ratelimit' => [
						'requests_per_unit' => 10,
						'unit'  => 'minute'
					]
				]
			],
			[
				new ClaimEntity( 'ratelimit',
					[
						'requests_per_unit' => 10,
						'unit'  => 'minute'
					]
				)
			]
		];

		yield 'tier name in db with empty default and tier config' => [
			'tier II',
			'',
			[],
			[]
		];
	}

	/**
	 * @dataProvider provideDatabaseTiers
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\Hooks::onOAuthClaimStoreGetClaims
	 * @param string|int $tierName
	 * @param string $defaultTierName
	 * @param array $tierConfig
	 * @param array $expectedClaimEntities
	 */
	public function testOnOAuthClaimStoreGetClaimsWithTiersInDB(
		$tierName, $defaultTierName, $tierConfig, $expectedClaimEntities
	) {
		$clientEntity = $this->getClientEntity();
		$this->assertTrue(
			$this->db->insert(
				'oauth_ratelimit_client_tier',
				[
					'oarct_client_id' => $clientEntity->getConsumerKey(),
					'oarct_tier_name' => $tierName,
				],
				__METHOD__
			),
			'Sanity: must add tier to database'
		);

		$this->setMwGlobals( 'wgOAuthRateLimiterDefaultClientTier', $defaultTierName );
		$this->setMwGlobals( 'wgOAuthRateLimiterTierConfig', $tierConfig );

		$claims = $this->claimStore->getClaims( 'dummyType', $clientEntity );
		$this->assertEquals( count( $expectedClaimEntities ), count( $claims ) );
		foreach ( $expectedClaimEntities as $index => $claimEntity ) {
			$this->assertEquals( $claimEntity->getName(), $claims[$index]->getName() );
			$this->assertEquals( $claimEntity->getValue(), $claims[$index]->getValue() );
		}
	}
}

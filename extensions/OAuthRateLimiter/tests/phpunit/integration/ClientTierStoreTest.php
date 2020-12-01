<?php

use MediaWiki\Extension\OAuthRateLimiter\ClientTierStore;
use MediaWiki\Extensions\OAuth\Entity\ClientEntity;
use MediaWiki\Extensions\OAuth\Tests\Entity\Mock_ClientEntity;
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @covers \MediaWiki\Extension\OAuthRateLimiter\ClientTierStore
 * @group Database
 */
class ClientTierStoreTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var ILoadBalancer
	 */
	private $loadBalancer;

	/**
	 * @var ClientTierStore
	 */
	private $clientTierStore;

	protected function setUp(): void {
		$this->loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$this->clientTierStore = new ClientTierStore( $this->loadBalancer );

		$this->tablesUsed[] = 'oauth_registered_consumer';
		$this->tablesUsed[] = 'oauth_ratelimit_client_tier';
	}

	private function getClientEntity() : ClientEntity {
		$clientEntity = Mock_ClientEntity::newMock( $this->getTestUser()->getUser() );
		$db = $this->loadBalancer->getConnectionRef( DB_MASTER );
		$this->assertTrue( $clientEntity->save( $db ), 'Sanity: must create a client' );

		return $clientEntity;
	}

	public function provideTiersDB() {
		yield 'Number tier name' => [
			9,
			'9'
		];

		yield 'String tier name' => [
			'Tier 1',
			'Tier 1'
		];

		yield 'Empty tier name' => [
			'',
			null
		];
	}

	/**
	 * @dataProvider provideTiersDB
	 * @param string|int $tierName
	 * @param string|null $expectedTierName
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\ClientTierStore::setClientTierName
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\ClientTierStore::getClientTierName
	 */
	public function testGetClientTierWithDBTiers( $tierName, $expectedTierName ) {
		$clientId = $this->getClientEntity()->getConsumerKey();

		$this->assertTrue(
			$this->clientTierStore->setClientTierName( $clientId, $tierName ),
			'Sanity: must add tier to database'
		);
		$tierNameFromStore = $this->clientTierStore->getClientTierName( $clientId );

		$this->assertSame( $expectedTierName, $tierNameFromStore );
	}

	/**
	 * @covers \MediaWiki\Extension\OAuthRateLimiter\ClientTierStore::getClientTierName
	 */
	public function testGetClientTierWithoutDBTiers() {
		$clientId = $this->getClientEntity()->getConsumerKey();
		$tierFromStore = $this->clientTierStore->getClientTierName( $clientId );

		$this->assertNull( $tierFromStore );
	}
}

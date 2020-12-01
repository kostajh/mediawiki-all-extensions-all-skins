<?php

namespace MediaWiki\Extension\OAuthRateLimiter;

use MediaWiki\Extensions\OAuth\Entity\ClaimEntity;
use MediaWiki\Extensions\OAuth\Entity\MWClientEntityInterface;
use MediaWiki\Extensions\OAuth\Repository\Hook\OAuthClaimStoreGetClaimsHook;

class Hooks implements OAuthClaimStoreGetClaimsHook {

	/**
	 * @var TierManager
	 */
	private $tierManager;

	/**
	 * @param TierManager $tierManager
	 */
	public function __construct( TierManager $tierManager ) {
		$this->tierManager = $tierManager;
	}

	/**
	 * @param string $grantType
	 * @param MWClientEntityInterface $clientEntity
	 * @param array &$privateClaims
	 * @param string|null $userIdentifier
	 */
	public function onOAuthClaimStoreGetClaims(
		string $grantType, MWClientEntityInterface $clientEntity, array &$privateClaims, $userIdentifier = null
	) {
		$clientID = $clientEntity->getIdentifier();
		$res = $this->tierManager->getClientTierConfig( $clientID );

		foreach ( $res as $name => $value ) {
			$privateClaims[] = new ClaimEntity( $name, $value );
		}
	}
}

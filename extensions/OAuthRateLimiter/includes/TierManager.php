<?php

namespace MediaWiki\Extension\OAuthRateLimiter;

use MediaWiki\Config\ServiceOptions;
use Psr\Log\LoggerInterface;

class TierManager {

	public const CONSTRUCTOR_OPTIONS = [
		'OAuthRateLimiterDefaultClientTier',
		'OAuthRateLimiterTierConfig'
	];

	/**
	 * @var ServiceOptions
	 */
	private $serviceOptions;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var ClientTierStore
	 */
	private $clientTierStore;

	/**
	 * @param ServiceOptions $serviceOptions
	 * @param LoggerInterface $logger
	 * @param ClientTierStore $clientTierStore
	 */
	public function __construct(
		ServiceOptions $serviceOptions,
		LoggerInterface $logger,
		ClientTierStore $clientTierStore
	) {
		$serviceOptions->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->serviceOptions = $serviceOptions;
		$this->logger = $logger;
		$this->clientTierStore = $clientTierStore;
	}

	/**
	 * @param string $clientID
	 * @return array
	 */
	public function getClientTierConfig( string $clientID ) : array {
		$tierName = $this->clientTierStore->getClientTierName( $clientID );
		$tierConfig = $this->serviceOptions->get( 'OAuthRateLimiterTierConfig' );

		if ( $tierName !== null && array_key_exists( $tierName, $tierConfig ) ) {
			return $tierConfig[$tierName];
		}

		$defaultTierName = $this->getDefaultTierName();
		if ( $defaultTierName === null ) {
			return [];
		}
		if ( array_key_exists( $defaultTierName, $tierConfig ) ) {
			return $tierConfig[$defaultTierName];
		}

		$this->logger->error(
			"wgOAuthRateLimiterTierConfig does not contain $tierName"
		);
		return [];
	}

	/**
	 * @return int|string|null
	 */
	public function getDefaultTierName() {
		$defaultClientTier = $this->serviceOptions->get( 'OAuthRateLimiterDefaultClientTier' );

		if ( $defaultClientTier ) {
			return $defaultClientTier;
		}

		$this->logger->error( 'wgOAuthRateLimiterDefaultClientTier is empty or not properly set' );
		return null;
	}
}

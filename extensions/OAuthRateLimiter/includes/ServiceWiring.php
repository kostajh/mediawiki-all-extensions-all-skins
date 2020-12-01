<?php

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\OAuthRateLimiter\ClientTierStore;
use MediaWiki\Extension\OAuthRateLimiter\TierManager;
use MediaWiki\Extensions\OAuth\Backend\Utils;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'OAuthRateLimiterTierManager' => function ( MediaWikiServices $services ) {
		return new TierManager(
			new ServiceOptions( TierManager::CONSTRUCTOR_OPTIONS, $services->getMainConfig() ),
			LoggerFactory::getInstance( 'OAuthRateLimiterTierManager' ),
			$services->getService( 'OAuthRateLimiterClientTierStore' )
		);
	},

	'OAuthRateLimiterClientTierStore' => function ( MediaWikiServices $services ) {
		return new ClientTierStore(
			$services->getDBLoadBalancerFactory()->getMainLB( Utils::getCentralWiki() )
		);
	}
];

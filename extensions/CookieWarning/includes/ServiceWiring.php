<?php

use CookieWarning\Decisions;
use CookieWarning\HttpGeoLocation;
use CookieWarning\NoopGeoLocation;
use MediaWiki\MediaWikiServices;

return [
	'CookieWarning.Config' => function ( MediaWikiServices $services ) {
		return $services->getService( 'ConfigFactory' )
			->makeConfig( 'cookiewarning' );
	},
	'GeoLocation' => function ( MediaWikiServices $services ) {
		$geoIPServiceURL = $services
			->getService( 'CookieWarning.Config' )
			->get( 'CookieWarningGeoIPServiceURL' );

		if ( !is_string( $geoIPServiceURL ) || !$geoIPServiceURL ) {
			return new NoopGeoLocation();
		}
		return new HttpGeoLocation( $geoIPServiceURL );
	},
	'CookieWarning.Decisions' => function ( MediaWikiServices $services ) {
		return new Decisions( $services->getService( 'CookieWarning.Config' ),
			$services->getService( 'GeoLocation' ), $services->getMainWANObjectCache() );
	},
];

<?php

use BlueSpice\WhoIsOnline\Tracer;
use MediaWiki\MediaWikiServices;

return [

	'BSWhoIsOnlineTracer' => function ( MediaWikiServices $services ) {
		return new Tracer(
			$services->getDBLoadBalancer(),
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getReadOnlyMode(),
			$services->getService( 'BSUtilityFactory' )
		);
	},

];

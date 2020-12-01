<?php

use BlueSpice\EchoConnector\UserLocator;
use MediaWiki\MediaWikiServices;

return [
	'BSEchoConnectorUserLocator' => function ( MediaWikiServices $services ) {
		return new UserLocator(
			$services->getDBLoadBalancer(),
			RequestContext::getMain(),
			$services->getPermissionManager(),
			$services->getHookContainer()
		);
	}
];

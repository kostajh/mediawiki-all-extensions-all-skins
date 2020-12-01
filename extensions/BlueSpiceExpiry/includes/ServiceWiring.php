<?php

use MediaWiki\MediaWikiServices;

return [

	'BSExpiryFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Expiry\Factory;
	},

];

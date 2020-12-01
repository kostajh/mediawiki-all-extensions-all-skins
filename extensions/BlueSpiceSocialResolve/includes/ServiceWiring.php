<?php

use MediaWiki\MediaWikiServices;

return [

	'BSSocialResolveFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\Resolve\Factory();
	},
];

<?php

use MediaWiki\MediaWikiServices;

return [
	'BSSocialAutoWatcherFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\Watch\AutoWatcherFactory();
	}
];

<?php

use MediaWiki\MediaWikiServices;

return [

	'BSSocialEntityListFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\EntityListFactory(
			$services->getService( 'BSRendererFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];

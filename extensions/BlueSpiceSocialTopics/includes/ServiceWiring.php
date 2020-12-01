<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [

	'BSSocialDiscussionEntityFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		return new \BlueSpice\Social\Topics\DiscussionFactory(
			$registry,
			$services->getService( 'BSEntityConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];

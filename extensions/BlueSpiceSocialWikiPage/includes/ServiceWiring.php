<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [

	'BSSocialWikiPageEntityFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		return new \BlueSpice\Social\WikiPage\WikiPageFactory(
			$registry,
			$services->getService( 'BSEntityConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];

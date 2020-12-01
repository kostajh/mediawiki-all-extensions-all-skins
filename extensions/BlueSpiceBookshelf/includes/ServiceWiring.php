<?php

use BlueSpice\Bookshelf\TreeParser;
use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [
	'BSBookshelfTreeParser' => function ( MediaWikiServices $services ) {
		$lineParsers = new ExtensionAttributeBasedRegistry( 'BlueSpiceBookshelfLineProcessors' );
		return new TreeParser(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$lineParsers
		);
	},
	'BSBookshelfPageHierarchyProviderFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Bookshelf\PageHierarchyProviderFactory();
	},
];

<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [
	'BSArticlePreviewCapturePhantomJSFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationPhantomJSBackendRegistry'
		);

		$backend = $services->getConfigFactory()->makeConfig( 'bsg' )
			->get( 'ArticlePreviewCapturePhantomJSBackend' );

		return new BlueSpice\ArticlePreviewCapture\PhantomJS\PhantomJSFactory(
			$registry,
			$backend
		);
	},
];

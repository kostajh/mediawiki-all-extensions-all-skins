<?php

use MediaWiki\Extension\FlexiSkin\FlexiSkinManager;
use MediaWiki\MediaWikiServices;

return [
	'FlexiSkinManager' => function ( MediaWikiServices $services ) {
		$path = $services->getMainConfig()->get( 'UploadDirectory' ) . '/flexiskin/';
		return new FlexiSkinManager( $path );
	}
];

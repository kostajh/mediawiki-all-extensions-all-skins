<?php

if ( version_compare( $GLOBALS['wgVersion'], '1.27c', '>' ) ) {
	if ( function_exists( 'wfLoadExtension' ) ) {
		wfLoadExtension( 'OpenLayers' );
		// Keep i18n globals so mergeMessageFileList.php doesn't break
		$GLOBALS['wgMessagesDirs']['OpenLayers'] = __DIR__ . '/i18n';
		/* wfWarn(
			'Deprecated PHP entry point used for OpenLayers extension. ' .
			'Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
		); */
		return;
	}
}

$GLOBALS['wgExtensionCredits']['other'][] = [
	'path' => __FILE__,
	'name' => 'OpenLayers',
	'version' => '1.0.0',
	'author' => [ 'Yaron Koren', 'Paladox', '...' ],
	'url' => 'https://www.mediawiki.org/wiki/Extension:OpenLayers',
	'descriptionmsg' => 'openlayers-desc',
	'license-name' => 'GPL-2.0-or-later'
];

// Register client-side modules.
$openlayersResourceTemplate = [
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'OpenLayers'
];
$GLOBALS['wgResourceModules'] += [
	'ext.openlayers.main' => $openlayersResourceTemplate + [
		'scripts' => [
			'libs/OpenLayers/OpenLayers.js'
		],
	],
];

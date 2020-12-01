<?php

/**
 * @license GPL-2.0-or-later
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

# Alert the user that this is not a valid access point to MediaWiki
# if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install LifeWeb, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/LifeWeb/LifeWeb.php" );
EOT;
	exit( 1 );
}

$wgExtensionCredits[ 'specialpage' ][] = [
	'path' => __FILE__,
	'name' => 'LifeWeb',
	'author' => 'Simon A. Eugster',
	'url' => 'https://www.mediawiki.org/wiki/Extension:LifeWeb',
	'descriptionmsg' => 'lifeweb-desc',
	'version' => '0.1.0',
];

# Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgAutoloadClasses[ 'SpecialLifeWeb' ] = __DIR__ . '/SpecialLifeWeb.php';
$wgMessagesDirs['LifeWeb'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles[ 'LifeWebAlias' ] = __DIR__ . '/LifeWeb.alias.php';
# Tell MediaWiki about the new special page and its class name
$wgSpecialPages[ 'LifeWeb' ] = 'SpecialLifeWeb';
$wgAPIListModules[ 'LifeWeb' ] = 'ApiLifeWeb';

$wgResourceModules['ext.LifeWeb.test'] = [
	'scripts' => [ 'test/test.js' ],
	'styles' => [],
	'messages' => [],
	'dependencies' => [
	],

	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LifeWeb',
];

$wgResourceModules['ext.LifeWeb.libLW'] = [
	'scripts' => [
		'lib/resources/JobList.js',
		'lib/resources/BaseItems.js',
		'lib/resources/libLW.wikibase.js',
	],
	'styles' => [],
	'messages' => [],
	'dependencies' => [
		'wikibase.api.RepoApi',
		'ext.LifeWebCore.core',
	],

	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LifeWeb',
];
$wgResourceModules['ext.LifeWeb.importLW'] = [
	'scripts' => [
		'lib/resources/JobList.js',
		'lib/resources/BaseItems.js',
		'lib/resources/importLW.js'
	],
	'styles' => [
	],
	'messages' => [],
	'dependencies' => [
		'wikibase.api.RepoApi',
		'ext.LifeWebCore.core',
	],

	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LifeWeb',
];
$wgResourceModules['ext.LifeWeb.editor'] = [
	'scripts' => [
		'lib/resources/loadEditor.js',
	],
	'styles' => [],
	'messages' => [],
	'dependencies' => [
		'ext.LifeWeb.libLW',
		'ext.LifeWebCore.editor',
	],

	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LifeWeb',
];
$wgResourceModules['ext.LifeWeb.filter'] = [
	'scripts' => [
		'lib/resources/loadFilter.js',
	],
	'styles' => [],
	'messages' => [],
	'dependencies' => [
		'ext.LifeWeb.libLW',
		'ext.LifeWebCore.filter',
	],

	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LifeWeb',
];
$wgLWSettings = [
	'topicIDs' => []
];

spl_autoload_register( function ( $className ) {
	$className = ltrim( $className, '\\' );

	$lastNsPos = strripos( $className, '\\' );
	if ( $lastNsPos ) {
		$namespace = substr( $className, 0, $lastNsPos );
		$className = substr( $className, $lastNsPos + 1 );
		$fileName = $namespace . '/';

		if ( $namespace == 'LifeWeb' ) {
			$fileName .= $className . '.php';

			require_once __DIR__ . '/lib/' . $fileName;
		}
	}
} );

require_once 'ApiLifeWeb.php';

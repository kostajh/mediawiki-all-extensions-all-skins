<?php
/**
 * My Skin skin
 *
 * @file
 * @ingroup Skins
 * @author Garrett LeSage
 */

if( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );
 
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'Strapping',
	'url' => "https://github.com/OSAS/strapping-mediawiki",
	'author' => 'Garrett LeSage',
	'descriptionmsg' => 'strapping-desc',
);

$wgValidSkinNames['strapping'] = 'Strapping';
$wgAutoloadClasses['SkinStrapping'] = __DIR__ . '/Strapping.skin.php';
$wgAutoloadClasses['StrappingTemplate'] = __DIR__ . '/Strapping.skin.php';
$wgMessagesDirs['SkinStrapping'] = __DIR__ . '/i18n';
 
$wgResourceModules['skins.strapping'] = array(
	'styles' => array(
		'strapping/bootstrap/css/bootstrap.css' => array( 'media' => 'screen' ),
		'strapping/bootstrap/css/bootstrap-responsive.css' => array( 'media' => 'screen' ),
		'strapping/bootstrap/awesome/css/font-awesome.css' => array( 'media' => 'screen' ),
		'strapping/screen.css' => array( 'media' => 'screen' ),
		'strapping/theme.css' => array( 'media' => 'screen' ),
	),
	'scripts' => array(
		'strapping/bootstrap/js/bootstrap.js',
		'strapping/strapping.js',
	),
	'remoteBasePath' => &$GLOBALS['wgStylePath'],
	'localBasePath' => &$GLOBALS['wgStyleDirectory'],
);

# Default options to customize skin
$wgStrappingSkinLogoLocation = 'bodycontent';
$wgStrappingSkinLoginLocation = 'footer';
$wgStrappingSkinAnonNavbar = false;
$wgStrappingSkinUseStandardLayout = true;
$wgStrappingSkinDisplaySidebarNavigation = false;

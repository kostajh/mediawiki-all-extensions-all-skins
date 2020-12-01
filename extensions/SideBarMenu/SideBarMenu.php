<?php

if (!defined('MEDIAWIKI')) {
	die('Not an entry point.');
}

if (!defined('ParamProcessor_VERSION')) {
	die('SideBarMenu requires extension ParamProcessor');
}

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

global $wgExtensionFunctions,$wgExtensionMessagesFiles, $wgExtensionCredits, $wgHooks, $wgResourceModules, $wgMessagesDirs;

//SideBarMenu constants
const SBM_EXPANDED = 'expanded';
const SBM_CONTROLS_SHOW = 'show';
const SBM_CONTROLS_HIDE = 'hide';
const SBM_JS_ANIMATE = 'animate';
const SBM_EDIT_LINK = 'editlink';
const SBM_CLASS = 'class';
const SBM_STYLE = 'style';
const SBM_MINIMIZED = 'minimized';

$wgSideBarMenuConstants = array(
	SBM_EXPANDED,
	SBM_CONTROLS_SHOW,
	SBM_CONTROLS_HIDE,
	SBM_JS_ANIMATE,
	SBM_EDIT_LINK,
	SBM_CLASS,
	SBM_STYLE,
	SBM_MINIMIZED,
);

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'SideBarMenu',
	'version' => '0.3.0',
	'author' => 'Kim Eik',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SideBarMenu',
	'descriptionmsg' => 'sidebarmenu-desc'
);

//i18n
$wgMessagesDirs['SideBarMenu'] = __DIR__ . '/i18n';

//Resources
$wgResourceModules['ext.sidebarmenu.core'] = array(
	'scripts' => array(
		'js/ext.sidebarmenu.js'
	),
	'styles' => array(
		'css/ext.sidebarmenu.css'
	),
	'dependencies' => array(
		'jquery.ui',
	),
	'messages' => array(
		'sidebarmenu-js-init-error'
	),
	'group' => 'ext.sidebarmenu',
	'localBasePath' => dirname(__FILE__),
	'remoteExtPath' => 'SideBarMenu'
);

$wgExtensionFunctions[] = function() {
	global $wgHooks;

	// Specify the function that will initialize the parser function.
	$wgHooks['ParserFirstCallInit'][] = 'SideBarMenu\Hooks::init';

	// Sepcify phpunit tests
	$wgHooks['UnitTestsList'][] = 'SideBarMenu\Hooks::registerUnitTests';
};

require_once(__DIR__.'/SideBarMenu.settings.php');

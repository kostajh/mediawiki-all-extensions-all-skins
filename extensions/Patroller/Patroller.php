<?php
/**
 * Patroller
 * Patroller MediaWiki main loader
 *
 * @author: Rob Church <robchur@gmail.com>, Kris Blair (Developaws)
 * @copyright: 2006-2008 Rob Church, 2015-2017 Kris Blair
 * @license: GPL General Public Licence 2.0
 * @package: Patroller
 * @link: https://mediawiki.org/wiki/Extension:Patroller
 */

$dir = __DIR__;

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Patroller' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Patroller'] = $dir . '/i18n';
	$wgExtensionMessagesFiles['PatrollerAlias'] = $dir . '/Patroller.alias.php';
	return true;
}

$wgExtensionCredits['specialpage'][] = [
	'path'				=> __FILE__,
	'name'				=> 'Patroller',
	'descriptionmsg'	=> 'patrol-desc',
	'author'			=> 'Rob Church, Kris Blair (Developaws)',
	'version'			=> '2.1',
	'url'				=> 'https://www.mediawiki.org/wiki/Extension:Patroller',
	'licence-name'		=> 'GPL-2.0-only'
];

// Register hooks
$wgAutoloadClasses['PatrollerHooks']			= $dir . '/Patroller.hooks.php';
$wgAutoloadClasses['SpecialPatroller']			= $dir . '/SpecialPatroller.php';

$wgHooks['LoadExtensionSchemaUpdates'][]		= 'PatrollerHooks::onLoadExtensionSchemaUpdates';
$wgSpecialPages['Patrol']						= 'SpecialPatroller';

// Register messages
$wgMessagesDir['Patroller']						= $dir . '/i18n';
$wgExtensionMessagesFiles['PatrollerAlias']		= $dir . '/Patroller.alias.php';

// Register rights
$wgAvailableRights[]							= 'patroller';
$wgGroupPermissions['sysop']['patroller']		= true;
$wgGroupPermissions['patroller']['patroller']	= true;

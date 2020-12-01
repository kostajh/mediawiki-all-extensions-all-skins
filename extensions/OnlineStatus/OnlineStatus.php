<?php

/**
 * Extension that adds a new toggle in user preferences to show if the user is
 * aviabled or not. See https://mediawiki.org/wiki/Extension:OnlineStatus for
 * more informations.
 * Require MediaWiki 1.17 r77011 or higher to work.
 *
 * @file
 * @ingroup Extensions
 * @author Alexandre Emsenhuber
 * @license GPL-2.0-or-later
 */

// Add credit :)
$wgExtensionCredits['other'][] = [
	'path'           => __FILE__,
	'name'           => 'OnlineStatus',
	'author'         => 'Alexandre Emsenhuber',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:OnlineStatus',
	'version'        => '2014-04-01',
	'descriptionmsg' => 'onlinestatus-desc',
	'license-name'   => 'GPL-2.0-or-later',
];

// Configuration

/**
 * Allow the {{#anyuseronlinestatus:}} parser function ?
 */
$wgAllowAnyUserOnlineStatusFunction = true;

/**
 * New preferences for this extension
 */
$wgDefaultUserOptions['online'] = 'online';
$wgDefaultUserOptions['showonline'] = 0;
$wgDefaultUserOptions['onlineonlogin'] = 1;
$wgDefaultUserOptions['offlineonlogout'] = 1;

$dir = __DIR__ . '/';

// Classes
$wgAutoloadClasses['OnlineStatus'] = $dir . 'OnlineStatus.body.php';

// Add messages files
$wgMessagesDirs['OnlineStatus'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['OnlineStatusMagic'] = $dir . 'OnlineStatus.i18n.magic.php';

// Hooks for the Parser
$wgHooks['ParserFirstCallInit'][] = 'OnlineStatus::ParserFirstCallInit';

// Magic words hooks
$wgHooks['MagicWordwgVariableIDs'][] = 'OnlineStatus::MagicWordVariable';
$wgHooks['ParserGetVariableValueSwitch'][] = 'OnlineStatus::ParserGetVariable';

// Hooks for Special:Preferences
$wgHooks['GetPreferences'][] = 'OnlineStatus::GetPreferences';

// User hook
$wgHooks['UserLoginComplete'][] = 'OnlineStatus::UserLoginComplete';
$wgHooks['UserLogoutComplete'][] = 'OnlineStatus::UserLogoutComplete';

// User page
$wgHooks['BeforePageDisplay'][] = 'OnlineStatus::BeforePageDisplay';
$wgHooks['PersonalUrls'][] = 'OnlineStatus::PersonalUrls';

// Ajax stuff
$wgAjaxExportList[] = 'OnlineStatus::Ajax';

$wgResourceModules['ext.onlineStatus'] = [
	'scripts' => 'OnlineStatus.js',
	'styles' => 'OnlineStatus.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'OnlineStatus',
];

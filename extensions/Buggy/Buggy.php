<?php
/**
 * Buggy extension - an extension that generates bugs.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @file
 * @ingroup Extensions
 * @license GNU General Public Licence 2.0 or later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Buggy' );
	wfWarn(
		'Deprecated PHP entry point used for Buggy extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
}

// Normal PHP setup follows for backwards compatability with older MW versions
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Buggy',
	'author' => array(
		'Gergő Tisza',
	),
	'version'  => '0.1.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Buggy',
	'descriptionmsg' => 'buggy-desc',
	'license-name' => 'GPL-2.0-or-later',
);

/* Setup */

// Register files
$wgAutoloadClasses['BuggyHooks'] = __DIR__ . '/Buggy.hooks.php';
$wgAutoloadClasses['ApiBuggy'] = __DIR__ . '/ApiBuggy.php';
$wgMessagesDirs['Buggy'] = __DIR__ . '/i18n';

// Register hooks
$wgHooks['BeforePageDisplay'][] = 'BuggyHooks::onBeforePageDisplay';

$wgAPIModules['buggy'] = 'ApiBuggy';

// Register modules
$wgBuggyPaths = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Buggy',
);
$wgResourceModules['ext.Buggy.css'] = $wgBuggyPaths + array( 'styles' => 'modules/ext.Buggy.css' );
$wgResourceModules['ext.Buggy.startup'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.startup.js' );
$wgResourceModules['ext.Buggy.setTimeout'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.setTimeout.js' );
$wgResourceModules['ext.Buggy.onready'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.onready.js' );
$wgResourceModules['ext.Buggy.click'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.click.js' );
$wgResourceModules['ext.Buggy.ajax'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.ajax.js' );
$wgResourceModules['ext.Buggy.misc-cb'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.misc-cb.js' );
$wgResourceModules['ext.Buggy.mwload'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.mwload.js' );
$wgResourceModules['ext.Buggy.logError'] = $wgBuggyPaths + array( 'scripts' => 'modules/ext.Buggy.logError.js' );

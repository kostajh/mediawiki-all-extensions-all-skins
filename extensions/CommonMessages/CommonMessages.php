<?php

/**
 * CommonMessages extension
 *
 * Allows a wikifarm to have custom
 * message overrides easilly
 * Mainly designed for ShoutWiki's setup,
 * but can be used for any farm.
 *
 * Copyright (C) 2014 Kunal Mehta <legoktm@member.fsf.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	exit();
}

$wgExtensionCredits['other'][] = [
	'path' => __FILE__,
	'name' => 'CommonMessages',
	'author' => 'Kunal Mehta',
	'descriptionmsg' => 'commonmessages-desc',
	'version' => '0.0.1',
	'license-name' => 'GPL-3.0-or-later',
];

/**
 * Prefix of messages when exporting.
 * Must be configured.
 */
$wgCommonMessagesPrefix = '';

/**
 * Directory of where to export messages to
 * and where to read from
 * Must already exist
 */
$wgCommonMessagesExportDir = __DIR__ . '/export';

$wgExtensionFunctions[] = function () {
	global $wgCommonMessagesExportDir;
	if ( file_exists( $wgCommonMessagesExportDir . '/en.json' ) ) {
		// If an export has happened, load it
		$wgMessagesDirs['CommonMessagesExport'] = $wgCommonMessagesExportDir;
	}
};

$wgMessagesDirs['CommonMessages'] = __DIR__ . '/i18n';
$wgAutoloadClasses['CommonMessages'] = __DIR__ . '/CommonMessages.body.php';

$wgHooks['MessageCache::get'][] = function ( &$key ) {
	$commons = CommonMessages::singleton();
	if ( $commons->isKeyRegistered( $key ) ) {
		$key = $commons->transformKey( $key );
	}

	return true;
};

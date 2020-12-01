<?php
#
# Author: Eric Petit (Surfzoid) - surfzoid@gmail.com
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html

// Code originally based on http://www.php-astux.info/script-compteur-telechargements.php

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'DownloadCounter',
	'version' => '0.4.0',
	'author' => 'Eric Petit',
	'descriptionmsg' => 'downloadcounter-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:DownloadCounter',
	'license-name' => 'GPL-2.0-or-later' // GNU General Public License v2.0 or later
);

/**
 * @param $input
 * @param $argv array
 * @param $parser Parser
 * @return int|string
 */
function DownloadCounter( $input, $argv, $parser ) {
	$parser->getOutput()->updateCacheExpiry( 0 );
	$file = $argv['name'];
	$details_type = $argv['type'];

	// Read the number or the last date of a downloaded file
	if ( $details_type == 'total' ) {
		$var = 'downloaded';
	} else {
		$var = 'last_download';
	}

	// Execute the query and return the result
	$db = wfGetDB( DB_MASTER );
	$res = $db->selectRow(
		'downloads_files',
		$var,
		array( 'filename' => $file ),
		__METHOD__
	);
	if ( $db->numRows( $res ) !== 1 ) { // File not found
		return 0;
	} else { // File found
		return ( $details_type == 'total' ) ? htmlspecialchars( $res->downloaded ) : htmlspecialchars( date( "d/m/Y H:i:s", $res->last_download ) );
	}
}

$wgMessagesDirs['DownloadCounter'] = __DIR__ . '/i18n';

$wgHooks['ParserFirstCallInit'][] = 'wfDownloadCounter';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'efDownloadCounterchemaUpdates';

/**
 * @param $parser Parser
 * @return bool
 */
function wfDownloadCounter( $parser ) {
	$parser->setHook( 'DownloadCounter', 'DownloadCounter' );
	return true;
}

/**
 * @param $updater DatabaseUpdater
 * @return bool
 */
function efDownloadCounterchemaUpdates( $updater ) {
	$updater->addExtensionTable( 'downloads_files', __DIR__ . '/DownloadCounter.sql' );
	return true;
}

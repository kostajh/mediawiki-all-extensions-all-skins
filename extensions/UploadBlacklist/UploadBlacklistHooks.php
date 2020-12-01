<?php

/**
 * Hooks for the UploadBlacklist extension.
 * Copyright (C) 2009; Brion VIBBER <brion@wikimedia.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class UploadBlacklistHooks {
	/**
	 * Callback for UploadVerification hook; calculates the file's
	 * SHA1 hash and checks it against a list of blacklisted files.
	 * If it matches, the upload will be denied.
	 *
	 * @param string $saveName Destination filename
	 * @param string $tempName Filesystem path to temporary upload file
	 * @param string &$error Set to HTML message if failure
	 * @return bool true if passes this check, false if blocked
	 */
	public static function onUploadVerification( $saveName, $tempName, &$error ) {
		$error = '';

		Wikimedia\suppressWarnings();
		$hash = sha1_file( $tempName );
		Wikimedia\restoreWarnings();

		if ( $hash === false ) {
			$error = "Failed to calculate file hash; may be missing or damaged.";
			$error .= " Filename: " . htmlspecialchars( $tempName );
			self::log( 'ERROR', $hash, $saveName, $tempName );
			return false;
		}

		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $ubUploadBlacklist;
		if ( in_array( $hash, $ubUploadBlacklist ) ) {
			$error = "File appears to be corrupt.";
			self::log( 'HIT', $hash, $saveName, $tempName );
			return false;
		} else {
			self::log( 'MISS', $hash, $saveName, $tempName );
			return true;
		}
	}

	/**
	 * Set $wgDebugLogGroups['UploadBlacklist'] to direct logging to a particular
	 * file instead of the debug log.
	 *
	 * @param string $action
	 * @param string $hash
	 * @param string $saveName
	 * @param string $tempName
	 */
	private static function log( $action, $hash, $saveName, $tempName ) {
		global $wgUser, $wgRequest;
		$user = $wgUser->getName();
		$ip = $wgRequest->getIP();
		$ts = wfTimestamp( TS_DB );
		wfDebugLog( 'UploadBlacklist', "$ts $action [$hash] name:$saveName file:$tempName user:$user ip:$ip" );
	}

}

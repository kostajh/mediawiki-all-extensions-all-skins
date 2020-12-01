<?php
/**
 * Copyright (C) 2005 Brion Vibber <brion@pobox.com>
 * https://www.mediawiki.org/
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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

/**
 * Requires real memcached with proper expiration semantics
 */

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'version'     => '0.3.0',
	'name' => 'UserThrottle',
	'author' => 'Brion Vibber',
	'descriptionmsg' => 'userthrottle-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:UserThrottle',
);

$wgHooks['AbortNewAccount'][] = 'throttleGlobalHit';
$wgMessagesDirs['UserThrottle'] = __DIR__ . '/i18n';

$wgGlobalAccountCreationThrottle = array(
	'min_interval' => 5,   // Hard minimum time between creations
	'soft_time'    => 300, // Timeout for rolling count
	'soft_limit'   => 10,  // 10 registrations in five minutes
);

/**
 * Hook function
 * @param User $user
 * @return bool false aborts registration, true allows
 * @static
 */
function throttleGlobalHit( $user ) {
	global $wgGlobalAccountCreationThrottle;

	$min_interval = $wgGlobalAccountCreationThrottle['min_interval'];
	$soft_limit = $wgGlobalAccountCreationThrottle['soft_limit'];
	$soft_time = $wgGlobalAccountCreationThrottle['soft_time'];
	$cache = ObjectCache::getLocalClusterInstance();

	if ( $min_interval > 0 ) {
		$key = $cache->makeKey( 'acctcreate-global-hard' );
		$cache->clearLastError();
		if ( !$cache->add( $key, 1, $min_interval ) && !$cache->getLastError() ) {
			// Key should have expired, or we're too close
			return throttleHardAbort( $min_interval );
		}
		throttleDebug( "hard limit ok (min_interval $min_interval)" );
	}

	if ( $soft_limit > 0 ) {
		$key = $cache->makeKey( 'acctcreate-global-soft' );
		$value = $cache->incrWithInit( $key, $soft_time );
		if ( $value > $soft_limit ) {
			// All registrations block until the limit rolls out
			return throttleSoftAbort( $soft_time, $soft_limit );
		}
		throttleDebug( "soft passed! ($value of soft_limit $soft_limit in $soft_time)" );
	}

	// Go ahead...
	return true;
}

function throttleSoftAbort( $interval, $limit ) {
	global $wgOut;
	throttleDebug( "softAbort: hit soft_limit $limit in soft_time $interval", true );
	if ( method_exists( $wgOut, 'addWikiTextAsInterface' ) ) {
		// MW 1.32+
		$wgOut->addWikiTextAsInterface( wfMessage( 'acct_creation_global_soft_throttle_hit', $interval, $limit )->text() );
	} else {
		$wgOut->addWikiText( wfMessage( 'acct_creation_global_soft_throttle_hit', $interval, $limit )->text() );
	}
	return false;
}

function throttleHardAbort( $interval ) {
	global $wgOut;
	throttleDebug( "hardAbort: hit min_interval $interval", true );
	if ( method_exists( $wgOut, 'addWikiTextAsInterface' ) ) {
		// MW 1.32+
		$wgOut->addWikiTextAsInterface( wfMessage( 'acct_creation_global_hard_throttle_hit', $interval )->text() );
	} else {
		$wgOut->addWikiText( wfMessage( 'acct_creation_global_hard_throttle_hit', $interval )->text() );
	}
	return false;
}

function throttleDebug( $text, $full = false ) {
	global $wgRequest;

	$info = '[IP: ' . $wgRequest->getIP() . ']';
	if ( function_exists( 'getallheaders' ) ) {
		$info .= '[headers: ' . implode( ' | ', array_map( 'urlencode', getallheaders() ) ) . ']';
	}
	wfDebugLog( 'UserThrottle', "UserThrottle: $text $info" );
}

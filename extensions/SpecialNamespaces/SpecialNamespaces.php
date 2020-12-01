<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This extension requires memcached and uses [[mw:Manual:Hooks/CanonicalNamespaces]]
 * see https://www.mediawiki.org/wiki/Extension:SpecialNamespaces
 *
 * SpecialNamespaces is an unsupported derivative work based on Special:Interwiki
 *
 * Authors of the original Interwiki extension were:
 * @author Stephanie Amanda Stevens <phroziac@gmail.com>
 * @author SPQRobin <robin_1273@hotmail.com>
 * @copyright Copyright (C) 2005-2007 Stephanie Amanda Stevens
 * @copyright Copyright (C) 2007 SPQRobin
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * Formatting improvements Stephen Kennedy, 2006.
 */

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\DBError;

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'SpecialNamespaces',
	'url'            => 'https://mediawiki.org/wiki/Extension:SpecialNamespaces',
	'author'         => array( 'Stephanie Amanda Stevens', 'SPQRobin', 'others' ),
	'descriptionmsg' => 'namespaces-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['Namespaces'] = $dir . 'i18n';
$wgExtensionMessagesFiles['NamespacesAlias'] = $dir . 'SpecialNamespaces.alias.php';

$wgSpecialPages['Namespaces'] = 'SpecialNamespaces';
$wgAutoloadClasses['SpecialNamespaces'] = $dir . 'SpecialNamespaces_body.php';

$wgAvailableRights[] = 'namespaces';
$wgGroupPermissions['sysop']['namespaces'] = true;

$wgLogTypes[] = 'namespaces';
$wgLogNames['namespaces'] = 'namespaces_logpagename';
$wgLogHeaders['namespaces'] = 'namespaces_logpagetext';
$wgLogActions['namespaces/namespaces'] = 'namespaces_logentry';
$wgLogActions['namespaces/ns_add'] = 'namespaces_log_added';
$wgLogActions['namespaces/ns_delete'] = 'namespaces_log_deleted';
$wgLogActions['namespaces/ns_edit'] = 'namespaces_log_edited';

# hook to retrieve $wgExtraNamespaces[...] from cached database table (namespace_names)
$wgHooks['CanonicalNamespaces'][] = 'fnNamespaceHook';

# hook for initial database table creation on MW 1.18+
$wgHooks['LoadExtensionSchemaUpdates'][] = 'fnNamespaceSchemaUpdates';


$wgResourceModules += array(
	'ext.specialnamespaces' => [
		'styles' => 'SpecialNamespaces.css',
		'localBasePath' => __DIR__ . '/modules',
		'remoteExtPath' => 'SpecialNamespaces/modules',
] );

/**
 * Database table creation (invoked in maintenance/update.php script)
 *
 * Installs a hook to call fnNamespaceCreateTables() to build an empty 'namespace_names' MySQL table.
 * If the table already exists, this code does nothing.
 */
function fnNamespaceSchemaUpdates( DatabaseUpdater $updater ) {
	$updater->addExtensionUpdate( array( 'fnNamespaceCreateTables' ) );
	return true;
}

/**
 * Create database table 'namespace_names' if it does not already exist (invoked from update.php)
 *
 * @param DatabaseUpdater $updater - the instance of update.php which initiated this installation process
 * @return (no return value) - output table (if created) is sent to main database with status to console
 */
function fnNamespaceCreateTables( $updater ) {
	global $wgDBtype;
	$base = dirname( __FILE__ );
	$db = $updater->getDB();

	if ( $db->tableExists( 'namespace_names' ) ) {
		$updater->output( "...namespace_names table already exists.\n" );
	} else {
		$sourcefile = $wgDBtype === 'postgres' ? '/namespace_names.pg.sql' : '/namespace_names.sql';
		$db->sourceFile( dirname( __FILE__ ) . $sourcefile );
	}
	$updater->output( "...namespace_names table added.\n" );
}

/**
 * Retrieve list of canonical namespaces from database table 'namespace_names'
 * Global namespace lists $wgExtraNamespaces[] and $wgNamespaceAliases[] are updated as needed
 *
 * This is a MediaWiki hook function, which uses the 'CanonicalNamespaces' hook in MW 1.17+
 * Use of memcached (where available) is necessary to avoid severe performance penalty for multiple db accesses
 *
 * @param array &$namespaces - the list of canonical namespaces retrieved from 'namespace_names' will be placed here
 * @return boolean true at all times (as a MediaWiki hook must always return a value)
 */
function fnNamespaceHook ( array &$namespaces ) {
	global $wgExtraNamespaces, $wgNamespaceAliases;
	global $wgDBname;
	global $wgSitename, $wgMetaNamespace, $wgMetaNamespaceTalk;

	if ( $wgExtraNamespaces == NULL ) {
	    $wgExtraNamespaces = array();
	}
	if ( $wgNamespaceAliases == NULL ) {
	    $wgNamespaceAliases = array();
	}

	$cache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
	$key = wfMemcKey( 'SpecialNamespaces', 'names' );
	$cached = $cache->get( $key );

	if ( ( $cached == NULL ) || ( !is_array( $cached ) ) ) {

		// if namespaces are not in memcache, retrieve them from main database
		$dbr = wfGetDB( DB_REPLICA );
		try {
			$res = $dbr->select( 'namespace_names', '*' );
		} catch ( DBError $e ) {
			// nasty hack to prevent the updater from breaking when it calls this hook
			if ( preg_match( "/Table '[^']*\bnamespace_names' doesn't exist/", $e->getMessage() ) ) {
				return;
			}
			throw $e;
		}
		$numrows = $dbr->numRows( $res );
		if ( $numrows > 0 )
		while ( $s = $dbr->fetchObject( $res ) ) {

			// for each namespace...
			$nsindex = htmlspecialchars( $s->ns_id );
			$nsname = htmlspecialchars( $s->ns_name );
			$nscanonical = htmlspecialchars( $s->ns_canonical );
			$nsdefault = htmlspecialchars( $s->ns_default );
			$nsname = str_replace( ' ', '_', $nsname );
			$nsname = str_replace( ':', '', $nsname );

			// add the namespace (or namespace alias) to global configuration variables
			if ( $nsdefault > 0 ) {
				$wgExtraNamespaces[$nsindex] = $nsname;
				if ( $nsindex == NS_PROJECT ) {
					$wgSitename = $nsname;
					$wgMetaNamespace = str_replace( ' ', '_', $nsname );
				}
				if ( $nsindex == NS_PROJECT_TALK ) {
					$wgMetaNamespaceTalk = str_replace( ' ', '_', $nsname );
				}
			} else {
				$wgNamespaceAliases[$nsname] = $nsindex;
			}

			// if canonical, add the namespace to list of canonical names in MW hook parameter
			if ( $nscanonical > 0 ) {
				$namespaces[$nsindex] = $nsname;
			}
		}
		$dbr->freeResult( $res );

		// store this info to memcache for re-use on subsequent page loads
		$cache->set ( $key,  array(
			'ns' => $namespaces,
			'ens' => $wgExtraNamespaces,
			'aka' => $wgNamespaceAliases,
			'site' => $wgSitename,
			'project' => $wgMetaNamespace,
			'prjtalk' => $wgMetaNamespaceTalk
		) );
	} else {
		// if data was retrieved from memcache, use it directly
		$namespaces = $cached['ns'];
		$wgExtraNamespaces = $cached['ens'];
		$wgNamespaceAliases = $cached['aka'];
		$wgSitename = $cached['site'];
		$wgMetaNamespace = $cached['project'];
		$wgMetaNamespaceTalk = $cached['prjtalk'];
	}
	return true;
}

<?php
/**
 * LogEntry extension
 *
 * @file
 * @ingroup Extensions
 *
 * This file contains the main include file for the LogEntry extension of
 * MediaWiki.
 *
 * Usage: Add the following line in LocalSettings.php:
 * wfLoadExtension( 'LogEntry' );
 *
 * @author Trevor Parscal <tparscal@wikimedia.org>
 * @license GPL-2.0-only
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'LogEntry' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['LogEntry'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the LogEntry extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the LogEntry extension requires MediaWiki 1.29+' );
}

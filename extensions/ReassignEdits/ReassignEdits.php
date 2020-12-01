<?php
/**
 * ReassignEdits
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author: Tim 'SVG' Weyer <SVG@Wikiunity.com>
 *
 * @copyright Copyright (C) 2011 Tim Weyer, Wikiunity
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ReassignEdits' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['ReassignEdits'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['ReassignEditsAliases'] = __DIR__ . '/ReassignEdits.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the ReassignEdits extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the ReassignEdits extension requires MediaWiki 1.29+' );
}

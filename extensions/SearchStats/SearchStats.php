<?php
/**
 * SearchStats extension
 *
 * For more info see https://mediawiki.org/wiki/Extension:SearchStats
 *
 * @file
 * @ingroup Extensions
 * @author Steven Orvis, 2016
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SearchStats' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SearchStats'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SearchStatsAlias'] = __DIR__ . '/SearchStats.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the SearchStats extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the SearchStats extension requires MediaWiki 1.29+' );
}

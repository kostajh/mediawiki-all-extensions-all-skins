<?php
/*
 * HelpPages extension
 * Fetches help pages from mediawiki.org if they
 * don't exist locally
 *
 * See also: https://www.mediawiki.org/wiki/Project:PD_help
 *
 * @file
 * @ingroup Extensions
 * @author Kunal Mehta
 * @license Public domain
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'HelpPages' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['HelpPages'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the HelpPages extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the HelpPages extension requires MediaWiki 1.29+' );
}

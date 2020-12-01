<?php

/**
 * LanguageTag extension from D250 Laboratories
 *
 * @file
 * @ingroup Extensions
 * @author István Király <LaKing@D250.hu>
 */

// Yes, this is an extension, not a standalone script, we are coming from LocalSettings.php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'LanguageTag' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['LanguageTag'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the LanguageTag extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the LanguageTag extension requires MediaWiki 1.29+' );
}

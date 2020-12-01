<?php

/**
 * Extension enabling the HSTS on a MediaWiki website on a per-user basis
 *
 * Note that if you intend to activate HSTS on the whole website, it will be
 * more efficient and robust to add it directly in the server configuration.
 *
 * @file
 * @ingroup Extensions
 * @author Seb35
 * @license WTFPL
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'HSTS' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['HSTS'] = __DIR__ . '/i18n';
	/* wfWarn(
		'Deprecated PHP entry point used for HSTS extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return;
} else {
	die( 'This version of the HSTS extension requires MediaWiki 1.25+' );
}

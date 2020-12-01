<?php

/**
 * Extension that use the PHP syntax highlighter (php -s)
 *
 * @author Alexandre Emsenhuber
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PhpHighlight' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PhpHighlight'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PhpHighlight extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PhpHighlight extension requires MediaWiki 1.25+' );
}

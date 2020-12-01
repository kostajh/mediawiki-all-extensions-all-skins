<?php
/**
 * @addtogroup Extensions
 *
 * @author Brian Wolff
 * @copyright Copyright © 2011 Brian Wolff
 * @license GPL-2.0-or-later
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SubpageSortkey' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SubpageSortkey'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for SubpageSortkey extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);

	return;
} else {
	die( 'This version of the SubpageSortkey extension requires MediaWiki 1.25+' );
}

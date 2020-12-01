<?php
/**
 * PaginateText extension - Paginates a long block of text.
 *
 * For more info see https://mediawiki.org/wiki/Extension:PaginateText
 *
 * @file
 * @ingroup Extensions
 * @author Ike Hecht, 2014
 * @license GPL-2.0-or-later
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PaginateText' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PaginateText'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PaginateText extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PaginateText extension requires MediaWiki 1.29+' );
}

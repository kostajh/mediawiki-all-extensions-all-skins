<?php
/**
 * Wrapper to integrate SVG-edit in-browser vector graphics editor in MediaWiki.
 * https://www.mediawiki.org/wiki/Extension:PopcornEditor
 *
 * Currently using a lot of base code form old SVGEdit extension.
 *
 * @copyright 2010-2020 Brion Vibber <brion@pobox.com>
 *
 * MediaWiki-side code is GPL
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PopcornEditor' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PopcornEditor'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PopcornEditor extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PopcornEditor extension requires MediaWiki 1.29+' );
}

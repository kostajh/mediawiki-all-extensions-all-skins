<?php
/**
 * Wrapper to integrate SVG-edit in-browser vector graphics editor in MediaWiki.
 * https://www.mediawiki.org/wiki/Extension:SVGEdit
 *
 * @copyright 2010 Brion Vibber <brion@pobox.com>
 *
 * MediaWiki-side code is GPL
 *
 * SVG-edit is under Apache license: http://code.google.com/p/svg-edit/
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SVGEdit' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SVGEdit'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the SVGEdit extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the SVGEdit extension requires MediaWiki 1.29+' );
}

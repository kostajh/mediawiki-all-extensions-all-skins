<?php
/**
 * ExtraLanguageLink extension, allowing arbitrary links to be added to the
 * "in other languages" sidebar section on a per-page basis
 *
 * Written by This, that and the other, 2014
 *
 * This code is released into the public domain. Anyone is allowed to use this
 * code for any purpose. (Needless to say, attribution is appreciated, although
 * not required.)
 *
 * For more information and documentation, see
 * https://www.mediawiki.org/wiki/Extension:ExtraLanguageLink
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ExtraLanguageLink' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['ExtraLanguageLink'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the ExtraLanguageLink extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the ExtraLanguageLink extension requires MediaWiki 1.29+' );
}

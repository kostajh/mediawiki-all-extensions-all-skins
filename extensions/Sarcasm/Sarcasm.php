<?php
/**
 * Sarcasm - this extension adds <sarcasm> tags to the wiki
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * wfLoadExtension( 'Sarcasm' );
 *
 * @ingroup Extensions
 * @author Inquisitor Ehrenstein <inquisitorsasha@sturmkrieg.de>
 * @link https://www.mediawiki.org/wiki/Extension:Sarcasm
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Sarcasm' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Sarcasm'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the Sarcasm extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Sarcasm extension requires MediaWiki 1.29+' );
}

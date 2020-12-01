<?php
/**
 * Extension to send email notifications to users on page creation
 *
 * @file
 * @author Nischay Nahata <nischayn22@gmail.com> for Wikiworks
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PageCreationNotif' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PageCreationNotif'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PageCreationNotif extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PageCreationNotif extension requires MediaWiki 1.29+' );
}

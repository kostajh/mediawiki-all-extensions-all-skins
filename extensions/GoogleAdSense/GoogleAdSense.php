<?php
/**
 * MediaWiki extension to add Google AdSense in a portlet in the sidebar.
 * Installation instructions can be found on
 * https://www.mediawiki.org/wiki/Extension:Google_AdSense_2
 *
 * This extension will not add the Google Adsense portlet to *any* skin
 * that is used with MediaWiki. Because of inconsistencies in the skin
 * implementation, it will not be add to the following skins:
 * cologneblue, standard, nostalgia
 *
 * @file
 * @ingroup Extensions
 * @author Siebrand Mazeland
 * @license MIT
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'GoogleAdSense' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['GoogleAdSense'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the GoogleAdSense extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the GoogleAdSense extension requires MediaWiki 1.29+' );
}

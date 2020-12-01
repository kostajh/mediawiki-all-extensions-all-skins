<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PanScroll' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PanScroll'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PanScroll extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PanScroll extension requires MediaWiki 1.29+' );
}

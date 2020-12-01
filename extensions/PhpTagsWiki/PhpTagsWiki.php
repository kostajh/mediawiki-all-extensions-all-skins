<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PhpTagsWiki' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PhpTagsWiki'] = __DIR__ . '/i18n';
//	wfWarn(
//		'Deprecated PHP entry point used for PhpTags Wiki extension. ' .
//		'Please use wfLoadExtension instead, ' .
//		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
//	);
	return;
} else {
	die( 'This version of the PhpTags Wiki extension requires MediaWiki 1.25+' );
}

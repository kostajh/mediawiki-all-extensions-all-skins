<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PhpTagsWidgets' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PhpTagsWidgets'] = __DIR__ . '/i18n';
//	wfWarn(
//		'Deprecated PHP entry point used for PhpTags Widgets extension. ' .
//		'Please use wfLoadExtension instead, ' .
//		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
//	);
	return;
} else {
	die( 'This version of the PhpTags Widgets extension requires MediaWiki 1.25+' );
}

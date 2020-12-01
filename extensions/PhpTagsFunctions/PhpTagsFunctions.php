<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PhpTagsFunctions' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PhpTagsFunctions'] = __DIR__ . '/i18n';
// wfWarn(
// 'Deprecated PHP entry point used for PhpTags Functions extension. ' .
// 'Please use wfLoadExtension instead, ' .
// 'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
// );
	return;
} else {
	die( 'This version of the PhpTags Functions extension requires MediaWiki 1.25+' );
}

<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PageBlock' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PageBlock'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PageBlock'] = __DIR__ . '/PageBlock.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the PageBlock extension. ' .
		'Please use wfLoadExtension instead. ' .
		'See https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the FooBar extension requires MediaWiki 1.25+' );
}

<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'IframePage' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['IframePage'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['IframePageAlias'] = __DIR__ . '/IframePage.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the IframePage extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the IframePage extension requires MediaWiki 1.29+' );
}

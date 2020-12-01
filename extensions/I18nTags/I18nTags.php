<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'I18nTags' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['I18nTags'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['I18nTagsMagic'] = __DIR__ . '/I18nTags.magic.php';
	/*wfWarn(
		'Deprecated PHP entry point used for I18nTags extension. ' .
		'Please use wfLoadExtension instead, see ' .
		'https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the I18nTags extension requires MediaWiki 1.25+' );
}

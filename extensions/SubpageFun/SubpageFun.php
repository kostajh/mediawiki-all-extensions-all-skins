<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SubpageFun' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SubpageFun'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SubpageFunMagic'] = __DIR__ . '/SubpageFun.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the SubpageFun extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the SubpageFun extension requires MediaWiki 1.25+' );
}

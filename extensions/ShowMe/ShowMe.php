<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ShowMe' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['ShowMe'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['ShowMeAlias'] = __DIR__ . '/ShowMe.i18n.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for ShowMe extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the ShowMe extension requires MediaWiki 1.25+' );
}

<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SiteSettings' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SiteSettings'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SiteSettingsAlias'] = __DIR__ . '/SiteSettings.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the SiteSettings extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the SiteSettings extension requires MediaWiki 1.25+' );
}

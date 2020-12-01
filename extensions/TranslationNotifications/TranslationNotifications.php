<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'TranslationNotifications' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['TranslationNotifications'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['TranslationNotificationsAlias'] =
		__DIR__ . '/TranslationNotifications.alias.php';

	wfWarn(
		'Deprecated PHP entry point used for TranslationNotifications extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the TranslationNotifications requires MediaWiki 1.25+' );
}

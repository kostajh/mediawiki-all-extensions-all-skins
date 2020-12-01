<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PreferencesList' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PreferencesList'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PreferencesListAlias'] = __DIR__ . '/PreferencesList.i18n.alias.php';
	$wgExtensionMessagesFiles['PreferencesListMagic'] = __DIR__ . '/PreferencesList.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for PreferencesList extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the PreferencesList extension requires MediaWiki 1.25+' );
}

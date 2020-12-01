<?php
/**
 * OdbcDatabase extension
 *
 * @author Roger Cass
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'OdbcDatabase' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['OdbcDatabase'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['OdbcDatabaseAlias'] = __DIR__ . '/OdbcDatabase.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the OdbcDatabase extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the OdbcDatabase extension requires MediaWiki 1.29+' );
}

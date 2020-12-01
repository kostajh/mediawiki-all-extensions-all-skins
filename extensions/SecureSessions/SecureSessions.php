<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SecureSessions' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SecureSessions'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SecureSessionsAlias'] = __DIR__ . '/SpecialSessions.alias.php';
	/*wfWarn(
		'Deprecated PHP entry point used for SecureSessions extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the SecureSessions extension requires MediaWiki 1.29+' );
}

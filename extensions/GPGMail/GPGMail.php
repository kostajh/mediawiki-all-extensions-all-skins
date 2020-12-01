<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'GPGMail' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['GPGMail'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for GPGMail extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'The GPGMail extension requires MediaWiki 1.25+' );
}

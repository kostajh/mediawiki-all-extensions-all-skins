<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'NamespacePopups' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	// $wgMessagesDirs['NamespacePopups'] = __DIR__ . '/i18n';
	/* wfWarn(
		'Deprecated PHP entry point used for NamespacePopups extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return;
} else {
	die( 'This version of the NamespacePopups extension requires MediaWiki 1.32+' );
}

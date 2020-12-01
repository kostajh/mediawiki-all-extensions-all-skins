<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Josa' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Josa'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['JosaMagic'] = __DIR__ . '/Josa.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for Josa extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Josa extension requires MediaWiki 1.25+' );
}

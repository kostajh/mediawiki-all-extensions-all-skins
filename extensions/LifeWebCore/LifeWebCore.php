<?php

/**
 * @license GPL-2.0-or-later
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'LifeWebCore' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['LifeWebCore'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the LifeWebCore extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the LifeWebCore extension requires MediaWiki 1.29+' );
}

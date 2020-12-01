<?php
/**
 * Link Attributes - easy modification of rel/rev/class on <a> elements.
 *
 * @author Toby Inkster <http://tobyinkster.co.uk/>
 * @license gpl-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Link_Attributes' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Link_Attributes'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for Link_Attributes extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the Link_Attributes extension requires MediaWiki 1.29+' );
}

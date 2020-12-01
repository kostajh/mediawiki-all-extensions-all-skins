<?php
/**
 * The Media Functions extension allows to retrieve various information about media files
 *
 * @link https://www.mediawiki.org/wiki/Extension:MediaFunctions Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:MediaFunctions Support
 * @link https://gerrit.wikimedia.org/r/p/mediawiki/extensions/MediaFunctions Source Code
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @author Rob Church (Robchurch) <robchur@gmail.com>
 *
 * @license https://www.opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'MediaFunctions' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MediaFunctions'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['MediaFunctionsMagic'] = __DIR__ . '/MediaFunctions.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the MediaFunctions extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the MediaFunctions extension requires MediaWiki 1.29+' );
}

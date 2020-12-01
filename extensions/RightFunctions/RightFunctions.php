<?php
/**
 * RightFunctions extension to MediaWiki
 *
 * Provides permission-based parser functions
 *
 * @link https://www.mediawiki.org/wiki/Extension:RightFunctions Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:RightFunctions Support
 * @link https://phabricator.wikimedia.org/diffusion/ERIF/ Source code
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @author Ryan Schmidt (Skizzerz)
 * @copyright (C) 2007 Ryan Schmidt
 * @license https://unlicense.org/ Unlicense
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'RightFunctions' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['RightFunctions'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['RightFunctionsMagic'] = __DIR__ . '/RightFunctions.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the RightFunctions extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the RightFunctions extension requires MediaWiki 1.33+' );
}

<?php
/**
 * Some functions to enable limited looping functionallity,
 * will also replace the text '$n$' or given parameter in the
 * given text to the current loop count plus one.
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @link https://www.mediawiki.org/wiki/Extension:LoopFunctions Documentation
 *
 * @author Carl Fürstenberg (AzaToth) <azatoth@gmail.com>
 * @copyright Copyright © 2006 Carl Fürstenberg, © 2008, 2012 Matteo Cypriani
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'LoopFunctions' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['LoopFunctions'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['LoopFunctionsMagic'] = __DIR__ . '/LoopFunctions.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the LoopFunctions extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the LoopFunctions extension requires MediaWiki 1.25+' );
}

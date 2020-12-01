<?php
/**
 * IfTemplates - Parser function that tests if a given page contains only template calls
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once('$IP/IfTemplates/IfTemplates.php');
 *
 * @ingroup Extensions
 * @author Ike Hecht
 * @version 0.1
 * @link https://www.mediawiki.org/wiki/Extension:IfTemplates Documentation
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'IfTemplates' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['IfTemplates'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['IfTemplatesMagic'] = __DIR__ . '/IfTemplates.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the IfTemplates extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the IfTemplates extension requires MediaWiki 1.29+' );
}

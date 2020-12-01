<?php
/**
 * Poll - Create a specialpage for using polls in MediaWiki.
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * wfLoadExtension( 'Poll' );
 *
 * @file
 * @ingroup Extensions
 * @author Jan Luca <jan@toolserver.org>
 * @version 1.0 (Beta)
 * @link https://www.mediawiki.org/wiki/Extension:Poll2 Documentation
 * @license CC-BY-SA-3.0
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Poll' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Poll'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PollAlias'] = __DIR__ . '/Poll.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the Poll extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Poll extension requires MediaWiki 1.29+' );
}

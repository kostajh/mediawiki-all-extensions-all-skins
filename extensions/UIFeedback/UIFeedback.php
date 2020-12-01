<?php
/**
 * UiFeedback Extension for MediaWiki.
 *
 * @file
 * @ingroup Extensions
 *
 * @license MIT
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'UIFEedback' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['UIFeedback'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['UIFeedbackAlias'] = __DIR__ . '/UIFeedback.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the UIFeedback extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the UIFeedback extension requires MediaWiki 1.29+' );
}

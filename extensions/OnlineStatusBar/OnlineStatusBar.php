<?php
/**
 * Insert a special box on user page showing their status.
 *
 * @file
 * @ingroup Extensions
 * @author Petr Bena <benapetr@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:OnlineStatusBar Documentation
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'OnlineStatusBar' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['OnlineStatusBar'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['OnlineStatusBarAlias'] = __DIR__ . '/OnlineStatusBar.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the OnlineStatusBar extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the OnlineStatusBar extension requires MediaWiki 1.29+' );
}

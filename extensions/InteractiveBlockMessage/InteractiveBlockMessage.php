<?php
/**
 * Insert a new magic word
 *
 * @file
 * @ingroup Extensions
 * @author Petr Bena <benapetr@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:InteractiveBlockMessage Documentation
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'InteractiveBlockMessage' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['InteractiveBlockMessage'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the InteractiveBlockMessage extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the InteractiveBlockMessage extension requires MediaWiki 1.25+' );
}

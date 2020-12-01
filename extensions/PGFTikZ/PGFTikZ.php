<?php

/**
 * PGFTikZ - this extension creates images from PGF/TikZ input (requires LaTeX
 * on the server).
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once('$IP/extensions/PGFTikZ.php');
 *
 * @ingroup Extensions
 * @authors Thibault Marin, Markus Bürkler
 * @version 0.1
 * @link https://www.mediawiki.org/wiki/Extension:PGFTikZ
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PGFTikZ' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PGFTikZ'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the PGFTikZ extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PGFTikZ extension requires MediaWiki 1.29+' );
}

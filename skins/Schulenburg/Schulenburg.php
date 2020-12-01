<?php
/**
 * Schulenburg skin.
 *
 * @file
 * @ingroup Skins
 * @author Tim Starling
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 * To install place the Schulenburg folder (the folder containing this file!) into
 * skins/ and add this line to your wiki's LocalSettings.php:
 * require_once("$IP/skins/Schulenburg/Schulenburg.php");
 */

if ( function_exists( 'wfLoadSkin' ) ) {
	wfLoadSkin( 'Schulenburg' );
	/*wfWarn(
		'Deprecated PHP entry point used for Schulenburg skin. Please use wfLoadSkin instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the Schulenburg skin requires MediaWiki 1.25+' );
}
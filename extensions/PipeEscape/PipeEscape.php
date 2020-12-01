<?php
/**
 * @file
 * @ingroup Extensions
 * @link https://www.mediawiki.org/wiki/Extension:Pipe_Escape Documentation
 *
 * @author David M. Sledge
 * @modifier Purodha Blissenbach
 * @copyright Copyright © 2008 David M. Sledge
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0
 *     or later
 * @version 0.1.0
 *     initial creation.
 * @version 0.1.1
 *     i18n support.
 * @version 2.0.0
 *     adaption to MediaWiki 1.26
 *
 * @todo
 *     allow alias names.
 */

// Ensure that the script cannot be executed outside of MediaWiki.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point.' );
}

// Load extension class.
$wgAutoloadClasses['ExtPipeEsc'] = __DIR__ . '/ExtPipeEsc.php';

// Display extension properties on MediaWiki.
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Pipe Escape',
	'namemsg' => 'pipeescape-extensionname',
	'author' => array(
		'David M. Sledge',
		'Purodha Blissenbach',
		'...'
		),
	'version' => '2.0.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Pipe_Escape',
	'descriptionmsg' => 'pipeescape-desc',
	'license-name' => 'GPL-2.0-or-later'
);

// Register extension messages and other localisation.
$wgMessagesDirs['PipeEscape'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['PipeEscapeMagic'] = __DIR__ . '/PipeEscape.i18n.magic.php';

// Register extension hooks.
$wgHooks[ 'ParserFirstCallInit' ][] = 'ExtPipeEsc::setup';

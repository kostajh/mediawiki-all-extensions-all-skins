<?php
/**
 * The PCR GUI Inserts extension lets you easily add pieces of HTML code
 * at several useful places of the GUI
 *
 * @version 2.0.4
 *
 * @link https://www.mediawiki.org/wiki/Extension:PCR_GUI_Inserts Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:PCR_GUI_Inserts Support
 * @link https://phabricator.wikimedia.org/diffusion/EPCR/ Source Code
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @author David Dernoncourt (Patheticcockroach)
 *
 * @license https://creativecommons.org/licenses/by-sa/4.0/ CC-BY-SA-4.0
 */

// Ensure that the script cannot be executed outside of MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) {
    die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Display extension's information on "Special:Version"
$wgExtensionCredits['other'][] = array(
	'path' => '__FILE__',
	'name' => 'PCR GUI Inserts',
	'version' => '2.0.4',
	'author' => array(
		'David Dernoncourt',
		'...'
		),
	'descriptionmsg' => 'pcrguiinserts-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:PCR_GUI_Inserts',
	'license-name' => 'CC-BY-SA-4.0'
);

// Define configuration parameter
$wgPCRguii_Inserts = array(
	'addHeadItem' => array(
		'on' => false,
		'content' => array(
			'head1',
			'head2'
		)
	),
	'BeforePageDisplay' => array(
		'on' => false,
		'content' => 'BeforePageDisplay'
	),
	'SkinAfterBottomScripts' => array(
		'on' => false,
		'content' => 'SkinAfterBottomScripts'
	),
	'SkinBuildSidebar' => array(
		'on' => false,
		'content' => array(
			array(
				'title1',
				'content1'
			),
			array(
				'title2',
				'content2'
			)
		)
	),
);

// Register extension messages
$wgMessagesDirs['PCRGUIInserts'] = __DIR__ . '/i18n';

// Register extension classs
$wgAutoloadClasses['PCRGUIInserts'] = __DIR__ . '/PCRGUIInserts.class.php';

// Register hooks
$wgHooks['ParserFirstCallInit'][] = 'wfPCRGUIInserts';

// Add extension function
function wfPCRGUIInserts() {
	new PCRGUIInserts;

	return true;
}

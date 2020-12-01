<?php
/**
 * @author Lia Veja
 * @copyright 2012 Lia Veja
 */
/*
	Copyright (c) 2012 Lia Veja

	This work is licensed under the Creative Commons
	Attribution-NonCommercial-ShareAlike 3.0
	Unported License. To view a copy of this license,
	visit http://creativecommons.org/licenses/by-nc-sa/3.0/
	or send a letter to Creative Commons,
	171 Second Street, Suite 300, San Francisco,
	California, 94105, USA.

 */
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This file is not a valid entry point.";
	exit( 1 );
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'OfflineImportLexicon',
	'version' => '0.3.0.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:OfflineImportLexicon',
	'descriptionmsg' => 'offlineimportlexicon-desc',
	'author' => array( 'Lia Veja' )
);

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['OfflineImportLexicon'] = 'OfflineImportLexicon';
$wgMessagesDirs['OfflineImportLexicon'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['OfflineImportLexiconAlias'] = $dir . 'OfflineImportLexicon.alias.php';
$wgAutoloadClasses['OfflineImportLexicon'] = $dir . 'OfflineImportLexicon_body.php';

$wgAvailableRights[] = 'offlineimportlexicon';
$wgGroupPermissions['user']['offlineimportlexicon'] = true;

// Hook things up
$wgHooks['BeforePageDisplay'][] = 'ResourcesJSJ';
$wgHooks['SkinAfterContent'][] = 'GetFormF';
if ( class_exists( MediaWiki\HookContainer\HookContainer::class ) ) {
	// MW 1.35+
	$wgHooks['PageSaveComplete'][] = 'CheckSave';
} else {
	$wgHooks['PageContentSaveComplete'][] = 'CheckSave';
}

$wgResourceModules['ext.OfflineImportLexicon'] = array(
	'scripts' => array(
		'scripts/OIL_Constants.js',
		'scripts/jquery.ezpz_tooltip.min.js',
		'scripts/OfflineImportLexicon.js',
		'scripts/OfflineImportLexiconSecond.js'
	),
	'styles' => array(
		'styles/OfflineImportLexicon.css'
	),
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'OfflineImportLexicon'
);

/**
 * Adds required JavaScript & CSS files to the HTML output of a page
 *
 * @param $out OutputPage object
 * @return true
 */
function ResourcesJSJ( OutputPage $out ) {
	global $wgTitle;

	if ( $wgTitle->getDBkey() == 'OfflineImportLexicon' ) {
		// Our custom CSS
		$out->addModules( array( 'ext.OfflineImportLexicon' ) );
	}
}

function GetFormF( &$data, $skin = null ) {
	global  $wgOut, $wgExtensionAssetsPath;

	if( is_null( $skin ) ) {
		global $wgTitle;
		$check = $wgTitle->getNamespace() == -1 && $wgTitle->getDBkey() == 'OfflineImportLexicon';
	} else {
		$check = $skin->getTitle()->isSpecial( 'OfflineImportLexicon' );
	}
	if( $check ) {
		$titleObj = SpecialPage::getTitleFor( 'OfflineImportLexicon' );
	}
	return true;
}

/**
 * WikiPage $wikiPage: the wikipage (object) saved
 * $user: the user (object) who saved the article
 * $text: the new article text
 * $summary: the article summary (comment)
 * $isminor: minor flag
 * $iswatch: watch flag
 * $section: section #
 */

function CheckSave( WikiPage $wikiPage, $user) {
	global $wgOut, $wgScriptPath;

	$articleId = $wikiPage->getID();
	$articleTitle = $wikiPage -> getTitle();
	$userId = $user->getName();

	$dbw = wfGetDB( DB_MASTER );

	$wgOut->addHTML(
		'<div id="lexicon-se" style="display:none;">'
		. '<p id="lexicon-se-editors">' . $articleTitle . '</p>'
		. '</div>'
	);
	//$wgOut->addScriptFile(  $wgScriptPath . '/NonSvnExtensions/OfflineImportLexicon/second.js'  );
	return true;
}

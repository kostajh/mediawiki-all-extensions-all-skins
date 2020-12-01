<?php
/*
 * Copyright (c) 2014 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

/**
 * To activate the functionality of this extension include the following
 * in your LocalSettings.php file:
 * MW 1.25+:
 * wfLoadExtension( "VikiTitleIcon" );
 * MW 1.23 and MW 1.24:
 * include_once "$IP/extensions/VikiTitleIcon/VikiTitleIcon.php";
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'VikiTitleIcon' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['VikiTitleIcon'] = __DIR__ . "/i18n";
	wfWarn(
		'Deprecated PHP entry point used for VikiTitleIcon extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( '<b>Error:</b> This file is part of a MediaWiki extension and cannot be run standalone.' );
}

if ( !defined( 'VIKIJS_VERSION' ) ) {
	die( '<b>Error:</b> The extension VikiTitleIcon requires ' .
		'VIKI to be installed first. Be sure that VIKI is included '
		. 'on a line ABOVE the line where you\'ve included VikiTitleIcon.' );
}

if ( version_compare( $wgVersion, '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of VikiTitleIcon '
		. 'is only compatible with MediaWiki 1.23 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> You need to have ' .
		'<a href="https://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a>' .
		' installed in order to use VikiTitleIcon.' );
}

if ( version_compare( SMW_VERSION, '1.9', '<' ) ) {
	die( '<b>Error:</b> VikiTitleIcon is only compatible with Semantic MediaWiki 1.9 or above.' );
}

$wgExtensionCredits['parserhook'][] = array (
	'path' => __FILE__,
	'name' => 'VikiTitleIcon',
	'version' => '1.4.0',
	'author' => '[https://www.mediawiki.org/wiki/User:Jji Jason Ji]',
	'descriptionmsg' => 'vikititleicon-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:VikiTitleIcon',
	'license-name' => 'MIT'
);

$wgMessagesDirs['VikiTitleIcon'] = __DIR__ . '/i18n';

$wgResourceModules['ext.VikiTitleIcon'] = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'VikiTitleIcon',
	'scripts' => array(
		'VikiTitleIcon.js'
	),
	'messages' => array(
		'vikititleicon-error-fetch'
	)
);

global $wgVIKI_Function_Hooks;

if ( !isset( $wgVIKI_Function_Hooks ) )
	$wgVIKI_Function_Hooks = array();

if ( array_key_exists( 'AfterVisitNodeHook', $wgVIKI_Function_Hooks ) )
	$wgVIKI_Function_Hooks['AfterVisitNodeHook'][] = 'VIKI.VikiTitleIcon.checkForTitleIcon';
else
	$wgVIKI_Function_Hooks['AfterVisitNodeHook'] = array( 'VIKI.VikiTitleIcon.checkForTitleIcon' );

$wgHooks['ParserFirstCallInit'][] = 'VikiTitleIcon::efVikiTitleIcon_AddResource';
$wgAPIModules['getTitleIcons'] = 'ApiGetTitleIcons';
$wgAutoloadClasses['ApiGetTitleIcons'] = __DIR__ . '/ApiGetTitleIcons.php';
$wgAutoloadClasses['VikiTitleIcon'] = __DIR__ . '/VikiTitleIcon_body.php';

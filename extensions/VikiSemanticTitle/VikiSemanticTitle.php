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
 * MW 1.25+
 * wfLoadExtension( "VikiSemanticTitle" );
 * MW 1.23 and 1.24
 * include_once "$IP/extensions/VikiSemanticTitle/VikiSemanticTitle.php";
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'VikiSemanticTitle' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['VikiSemanticTitle'] = __DIR__ . "/i18n";
	wfWarn(
		'Deprecated PHP entry point used for VikiSemanticTitle extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( '<b>Error:</b> This file is part of a MediaWiki extension and cannot be run standalone.' );
}

if ( !defined( 'VIKIJS_VERSION' ) ) {
	die( '<b>Error:</b> The extension VikiSemanticTitle requires ' .
		'VIKI to be installed first. Be sure that VIKI is included '
		. 'on a line ABOVE the line where you\'ve included VikiSemanticTitle.' );
}

if ( version_compare( $wgVersion, '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of VikiSemanticTitle '
		. 'is only compatible with MediaWiki 1.23 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> You need to have ' .
		'<a href="https://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a>' .
		' installed in order to use VikiSemanticTitle.' );
}

if ( version_compare( SMW_VERSION, '1.9', '<' ) ) {
	die( '<b>Error:</b> VikiSemanticTitle is only compatible with Semantic MediaWiki 1.9 or above.' );
}

$wgExtensionCredits['parserhook'][] = array (
	'name' => 'VikiSemanticTitle',
	'version' => '1.4.0',
	'author' => '[https://www.mediawiki.org/wiki/User:Jji Jason Ji]',
	'descriptionmsg' => 'vikisemantictitle-desc',
	'path' => __FILE__,
	'url' => 'https://www.mediawiki.org/wiki/Extension:VikiSemanticTitle',
	'license-name' => 'MIT'
);

$wgMessagesDirs['VikiSemanticTitle'] = __DIR__ . '/i18n';

$wgResourceModules['ext.VikiSemanticTitle'] = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'VikiSemanticTitle',
	'scripts' => array(
		'VikiSemanticTitle.js'
	),
	'dependencies' => array(
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'vikisemantictitle-error-displaytitle-fetch'
	)
);

global $wgVIKI_Function_Hooks;

if ( !isset( $wgVIKI_Function_Hooks ) )
	$wgVIKI_Function_Hooks = array();

if ( array_key_exists( 'AfterVisitNodeHook', $wgVIKI_Function_Hooks ) )
	$wgVIKI_Function_Hooks['AfterVisitNodeHook'][] = 'VIKI.VikiSemanticTitle.checkForSemanticTitle';
else
	$wgVIKI_Function_Hooks['AfterVisitNodeHook'] =
		array( 'VIKI.VikiSemanticTitle.checkForSemanticTitle' );

$wgHooks['ParserFirstCallInit'][] = 'VikiSemanticTitle::efVikiSemanticTitle_AddResource';
$wgAutoloadClasses['VikiSemanticTitle'] = __DIR__ . '/VikiSemanticTitle_body.php';

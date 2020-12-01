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
 * wfLoadExtension( "VIKI" );
 * $wgRegisterInternalExternals = true;
 *
 * MW 1.23 and 1.24:
 * include_once "$IP/extensions/VIKI/VIKI.php";
 * $wgRegisterInternalExternals = true;
 *
 * If $wgRegisterInternalExternals was not already true, you must run
 * refreshLinks.php after setting this flag.
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'VIKI' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['VIKI'] = __DIR__ . "/i18n";
	$wgExtensionMessagesFiles['VIKIMagic'] = __DIR__ . '/VIKI.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for VIKI extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
}

define( 'VIKIJS_VERSION', '1.6.0' );

if ( !defined( 'MEDIAWIKI' ) ) {
	die( '<b>Error:</b> This file is part of a MediaWiki extension and cannot be run standalone.' );
}

if ( version_compare( $wgVersion, '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of VIKI is only compatible with MediaWiki 1.23 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> You need to have ' .
		'<a href="https://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a>' .
		' installed in order to use VIKI.' );
}

if ( version_compare( SMW_VERSION, '1.9', '<' ) ) {
	die( '<b>Error:</b> VIKI is only compatible with Semantic MediaWiki 1.9 or above.' );
}

$wgExtensionCredits['parserhook'][] = array (
	'name' => 'VIKI',
	'version' => VIKIJS_VERSION,
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:Jji Jason Ji]',
		'[https://www.mediawiki.org/wiki/User:Cindy.cicalese Cindy Cicalese]'
	),
	'descriptionmsg' => 'viki-desc',
	'path' => __FILE__,
	'url' => 'https://www.mediawiki.org/wiki/Extension:VIKI',
	'license-name' => 'MIT'
);

$wgExtensionMessagesFiles['VIKIMagic'] =
	__DIR__ . '/VIKI.i18n.magic.php';

$wgMessagesDirs['VIKI'] = __DIR__ . '/i18n';

$wgResourceModules['ext.VIKI'] = array(
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'VIKI',
	'styles' => array(
		'VIKI.css',
		'vex.css',
		'vex-theme-default.css'
	),
	'scripts' => array(
		'd3.v3.js',
		'vex.combined.min.js',
		'spin.min.js',
		'contextmenu.js',
		'VIKI.js'
	),
	'dependencies' => array(
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
	'viki-error-title',
	'viki-error-missing-pageTitle',
	'viki-timeout-content-namespace',
	'viki-error-content-namespace',
	'viki-error-visit-node',
	'viki-error-external-links',
	'viki-error-intrawiki-out',
	'viki-error-intrawiki-in',
	'viki-error-https-node',
	'viki-error-https-namespace'
	)
);

$wgHooks['ParserFirstCallInit'][] = 'VikiJS::efVIKIParserFunction_Setup';

$wgAPIModules['getContentNamespaces'] = 'ApiGetContentNamespaces';

$wgAutoloadClasses['VikiJS'] = __DIR__ . '/VikiJS.class.php';
$wgAutoloadClasses['ApiGetContentNamespaces'] = __DIR__ . '/ApiGetContentNamespaces.php';

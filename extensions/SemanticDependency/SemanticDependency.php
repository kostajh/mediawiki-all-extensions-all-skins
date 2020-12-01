<?php
/**
 * Copyright (c) 2014-2015 The MITRE Corporation
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

// Ensure that the script cannot be executed outside of MediaWiki.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is an extension to the MediaWiki package and cannot be run standalone." );
}

// Display extension properties on MediaWiki.
$GLOBALS['wgExtensionCredits']['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Dependency',
	'version' => '1.2',
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:Cindy.cicalese Cindy Cicalese]'
	),
	'descriptionmsg' => 'semanticdependency-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Semantic_Dependency',
	'license-name' => 'MIT'
);

// Load extension's classes.
$GLOBALS['wgAutoloadClasses']['SemanticDependency'] =
	__DIR__ . '/SemanticDependency.class.php';

// Register extension hooks.
$GLOBALS['wgHooks']['SMWStore::updateDataAfter'][] =
	'SemanticDependency::updateDataAfter';
$GLOBALS['wgHooks']['ArticleDelete'][] = 'SemanticDependency::articleDelete';
$GLOBALS['wgHooks']['ArticleDeleteComplete'][] =
	'SemanticDependency::articleDeleteComplete';

// Register extension messages and other localisation.
$GLOBALS['wgMessagesDirs']['SemanticDependency'] = __DIR__ . '/i18n';

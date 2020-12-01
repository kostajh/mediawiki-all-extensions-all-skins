<?php

/**
 * An extension creating a series of pages from one Semantic Form.
 *
 * @defgroup SemanticPageSeries Semantic Page Series
 * @author Stephan Gambke
 */
/**
 * The main file of the SemanticPageSeries extension
 *
 * @author Stephan Gambke
 *
 * @file
 * @ingroup SemanticPageSeries
 */

// Ensure that the script cannot be executed outside of MediaWiki.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of a MediaWiki extension, it is not a valid entry point.' );
}

// Do compatibility checks with connected software
if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> <a href="https://www.mediawiki.org/wiki/Extension:Semantic_Page_Series">Semantic Page Series</a> depends on the Semantic MediaWiki extension. You need to install <a href="https://www.mediawiki.org/wiki/Extension:Semantic_MediaWiki">Semantic MediaWiki</a> first.' );
}

if ( !defined( 'SF_VERSION' ) ) {
	die( '<b>Error:</b> <a href="https://www.mediawiki.org/wiki/Extension:Semantic_Page_Series">Semantic Page Series</a> depends on the Semantic Forms extension. You need to install <a href="https://www.mediawiki.org/wiki/Extension:Semantic_Forms">Semantic Forms</a> first.' );
}

// Set the extension's version
define( 'SPS_VERSION', '0.4.0' );

// register the extension
$wgExtensionCredits['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Page Series',
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:F.trott Stephan Gambke]',
		'...'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Semantic_Page_Series',
	'descriptionmsg' => 'semanticpageseries-desc',
	'version' => SPS_VERSION,
	'license-name' => 'GPL-2.0-or-later'
);

// register message files
$wgMessagesDirs['SemanticPageSeries'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SemanticPageSeriesMagic'] = __DIR__ . '/SemanticPageSeries.magic.php';
$wgExtensionMessagesFiles['SemanticPageSeriesAlias'] = __DIR__ . '/SemanticPageSeries.alias.php';

// register class files with the Autoloader
$wgAutoloadClasses['SPSUtils'] = __DIR__ . '/includes/SPSUtils.php';
$wgAutoloadClasses['SPSSpecialSeriesEdit'] = __DIR__ . '/includes/SPSSpecialSeriesEdit.php';
$wgAutoloadClasses['SPSException'] = __DIR__ . '/includes/SPSException.php';
$wgAutoloadClasses['SPSPageCreationJob'] = __DIR__ . '/includes/SPSPageCreationJob.php';

$wgAutoloadClasses['SPSIterator'] = __DIR__ . '/includes/iterators/SPSIterator.php';
$wgAutoloadClasses['SPSDateIterator'] = __DIR__ . '/includes/iterators/SPSDateIterator.php';
$wgAutoloadClasses['SPSCountIterator'] = __DIR__ . '/includes/iterators/SPSCountIterator.php';
$wgAutoloadClasses['SPSPageIterator'] = __DIR__ . '/includes/iterators/SPSPageIterator.php';

// register Special page
$wgSpecialPages['SeriesEdit'] = 'SPSSpecialSeriesEdit'; // Tell MediaWiki about the new special page and its class name

// register hook handlers

// Specify the function that will initialize the parser function.
$wgHooks['ParserFirstCallInit'][] = 'SPSUtils::initParserFunction';

// define constants
define('SPS_NOLIMIT', PHP_INT_MAX);

// register iterators
$spsgIterators = array (
	'date' => 'SPSDateIterator',
	'count' => 'SPSCountIterator',
	'page' => 'SPSPageIterator',
);

$spsgPageGenerationLimits = array(
	'*' => 0,
	'user' => 10,
	'sysop' => SPS_NOLIMIT
);

$wgJobClasses['spsCreatePage'] = 'SPSPageCreationJob';

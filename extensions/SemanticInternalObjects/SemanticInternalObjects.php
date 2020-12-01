<?php
/**
 * Initialization file for Semantic Internal Objects.
 *
 * @file
 * @ingroup SemanticInternalObjects
 * @author Yaron Koren
 */

// Ensure that the script cannot be executed outside of MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) {
    die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Define extension's version
define( 'SIO_VERSION', '0.8.2' );

// Display extension's information on "Special:Version"
$wgExtensionCredits['semantic'][] = array(
	'path' => __FILE__,
	'name'	=> 'Semantic Internal Objects',
	'version' => SIO_VERSION,
	'author' => 'Yaron Koren',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Semantic_Internal_Objects',
	'descriptionmsg' => 'semanticinternalobjects-desc',
	'license-name' => 'GPL-2.0-or-later'
);

// If we're using SMWSQLStore3 (introduced in SMW 1.8), just
// call SMW's own #subobject, which has a similar, though not
// identical, syntax.
// Eventually, it may make sense to just turn #set_internal
// into a quasi-alias for #subobject in all cases.
if ( $smwgDefaultStore == 'SMWSQLStore3' ) {
	$wgHooks['ParserFirstCallInit'][] = 'siofRegisterAliasParserFunctions';
} else {
	$wgHooks['ParserFirstCallInit'][] = 'siofRegisterParserFunctions';
	$wgHooks['ParserClearState'][] = 'SIOHandler::clearState';
	$wgHooks['SMWSQLStore2::updateDataAfter'][] = 'SIOHandler::updateData';
	$wgHooks['SMWSQLStore2::deleteSubjectAfter'][] = 'SIOHandler::deleteData';
	$wgHooks['smwUpdatePropertySubjects'][] = 'SIOHandler::handleUpdatingOfInternalObjects';
	$wgHooks['TitleMoveComplete'][] = 'SIOHandler::handlePageMove';
	$wgHooks['smwRefreshDataJobs'][] = 'SIOHandler::handleRefreshingOfInternalObjects';
	$wgHooks['smwAddToRDFExport'][] = 'SIOSQLStore::createRDF';
	$wgAutoloadClasses['SIOSQLStore'] = __DIR__ . '/SemanticInternalObjects_body.php';
	if ( class_exists( 'SMWDIWikiPage' ) ) {
		// SMW >= 1.6
		$wgAutoloadClasses['SIOInternalObjectValue'] = __DIR__ . '/SIO_RDFClasses2.php';
		$wgAutoloadClasses['SIOTitle'] = __DIR__ . '/SIO_RDFClasses2.php';
	} else {
		$wgAutoloadClasses['SIOInternalObjectValue'] = __DIR__ . '/SIO_RDFClasses.php';
		$wgAutoloadClasses['SIOTitle'] = __DIR__ . '/SIO_RDFClasses.php';
		$wgAutoloadClasses['SIOExporter'] = __DIR__ . '/SIO_RDFClasses.php';
	}
}

$wgHooks['PageSchemasRegisterHandlers'][] = 'SIOPageSchemas::registerClass';

$wgMessagesDirs['SemanticInternalObjects'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SemanticInternalObjectsMagic'] = __DIR__ . '/SemanticInternalObjects.i18n.magic.php';
$wgAutoloadClasses['SIOHandler'] = __DIR__ . '/SemanticInternalObjects_body.php';
$wgAutoloadClasses['SIOInternalObject'] = __DIR__ . '/SemanticInternalObjects_body.php';
$wgAutoloadClasses['SIOSubobjectAlias'] = __DIR__ . '/SIO_SubobjectAlias.php';
$wgAutoloadClasses['SIOPageSchemas'] = __DIR__ . '/SIO_PageSchemas.php';

function siofRegisterParserFunctions( &$parser ) {
	$parser->setFunctionHook( 'set_internal', array( 'SIOHandler', 'doSetInternal' ) );
	$parser->setFunctionHook( 'set_internal_recurring_event', array( 'SIOHandler', 'doSetInternalRecurringEvent' ) );
	return true; // always return true, in order not to stop MW's hook processing!
}

function siofRegisterAliasParserFunctions( &$parser ) {
	$parser->setFunctionHook( 'set_internal', array( 'SIOSubobjectAlias', 'doSetInternal' ) );
	$parser->setFunctionHook( 'set_internal_recurring_event', array( 'SIOSubobjectAlias', 'doSetInternalRecurringEvent' ) );
	return true; // always return true, in order not to stop MW's hook processing!
}

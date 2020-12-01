<?php
/**
 * Main entry point for the PhpTags Storage extension.
 *
 * @link https://www.mediawiki.org/wiki/Extension:PhpTags_Storage Documentation
 * @file PhpTagsStorage.php
 * @defgroup PhpTags
 * @ingroup Extensions
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */

// Check to see if we are being called as an extension or directly
if ( !defined('MEDIAWIKI') ) {
	die( 'This file is an extension to MediaWiki and thus not a valid entry point.' );
}

const PHPTAGS_STORAGE_VERSION = '0.1.1';

// Register this extension on Special:Version
$wgExtensionCredits['phptags'][] = array(
	'path' => __FILE__,
	'name' => 'PhpTags Storage',
	'version' => PHPTAGS_STORAGE_VERSION,
	'url' => 'https://www.mediawiki.org/wiki/Extension:PhpTags_Storage',
	'author' => '[https://www.mediawiki.org/wiki/User:Pastakhov Pavel Astakhov]',
	'descriptionmsg' => 'phptagsstorage-desc',
	'license-name' => 'GPL-2.0-or-later',
);

// Allow translations for this extension
$wgMessagesDirs['PhpTagsStorage'] = __DIR__ . '/i18n';

// Add hooks
$wgHooks['ParserFirstCallInit'][] = 'PhpTagsStorageHooks::onParserFirstCallInit';
$wgHooks['PhpTagsRuntimeFirstInit'][] = 'PhpTagsStorageHooks::onPhpTagsRuntimeFirstInit';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PhpTagsStorageHooks::onLoadExtensionSchemaUpdates';
$wgHooks['SecondaryDataUpdates'][] = 'PhpTagsStorageHooks::onSecondaryDataUpdates';
$wgHooks['WikiPageDeletionUpdates'][] = 'PhpTagsStorageHooks::onWikiPageDeletionUpdates';
$wgHooks['UnitTestsList'][] = 'PhpTagsStorageHooks::onUnitTestsList';
$wgHooks['ParserTestTables'][] = 'PhpTagsStorageHooks::onParserTestTables';

// Preparing classes for autoloading
$wgAutoloadClasses['PhpTagsStorageHooks'] = __DIR__ . '/PhpTagsStorage.hooks.php';
$wgAutoloadClasses['PhpTagsObjects\\Storage'] = __DIR__ . '/includes/Storage.php';
$wgAutoloadClasses['PhpTagsObjects\\PageData'] = __DIR__ . '/includes/PageData.php';
$wgAutoloadClasses['PhpTagsStorage\\Schema'] = __DIR__ . '/includes/Storage/Schema.php';
$wgAutoloadClasses['PhpTagsStorage\\SchemaUpdate'] = __DIR__ . '/includes/Storage/SchemaUpdate.php';
$wgAutoloadClasses['PhpTagsStorage\\PageDataUpdate'] = __DIR__ . '/includes/Storage/PageDataUpdate.php';
$wgAutoloadClasses['PhpTagsStorage\\PageTemplatesUpdate'] = __DIR__ . '/includes/Storage/PageTemplatesUpdate.php';
$wgAutoloadClasses['PhpTagsStorage\\Field'] = __DIR__ . '/includes/Storage/Field.php';

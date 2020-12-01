<?php


/**
 * PhpTags Storage MediaWiki Hooks.
 *
 * @file PhpTagsStorage.hooks.php
 * @ingroup PhpTags
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class PhpTagsStorageHooks {

	/**
	 *
	 * @return boolean
	 */
	public static function onParserFirstCallInit() {
		if ( !defined( 'PHPTAGS_VERSION' ) ) {
			throw new MWException( "\n\nYou need to have the PhpTags extension installed in order to use the PhpTags Storage extension." );
		}
		$needVersion = '5.1.4';
		if ( version_compare( PHPTAGS_VERSION, $needVersion, '<' ) ) {
			throw new MWException( "\n\nThis version of the PhpTags Storage extension requires the PhpTags extension $needVersion or above.\n You have " . PHPTAGS_VERSION . ". Please update it." );
		}
		if ( PHPTAGS_HOOK_RELEASE != 8 ) {
			throw new MWException( "\n\nThis version of the PhpTags Storage extension is outdated and not compatible with current version of the PhpTags extension.\n Please update it." );
		}
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public static function onPhpTagsRuntimeFirstInit() {
		\PhpTags\Hooks::addJsonFile( __DIR__ . '/PhpTagsStorage.json', PHPTAGS_STORAGE_VERSION );
		return true;
	}

	/**
	 *
	 * @param \Title $title
	 * @param \Content $old
	 * @param bool $recursive
	 * @param \ParserOutput $parserOutput
	 * @param array $updates
	 * @return boolean
	 */
	public static function onSecondaryDataUpdates( $title, $old, $recursive, $parserOutput, &$updates ) {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );
//		echo __METHOD__ . '( ' . $title->getArticleID() . " )\n";
		\PhpTagsObjects\Storage::onDataUpdates( $title->getArticleID(), $updates );
		return true;
	}

	/**
	 *
	 * @param \WikiPage $page
	 * @param type $content
	 * @param array $updates
	 */
	public static function onWikiPageDeletionUpdates( WikiPage $page, $content, &$updates ) {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );
		$titleID = $page->getTitle()->getArticleID();
//		echo __METHOD__ . '( ' . $titleID . " )\n";
		PhpTagsStorage\Schema::onPageDelete( $titleID );
		PhpTagsObjects\Storage::onDataUpdates( $titleID, $updates );
	}

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'phptags_schemas', __DIR__ . '/sql/storage.sql' );
		$updater->addExtensionTable( 'phptags_page_templates', __DIR__ . '/sql/storage.sql' );
		return true;
	}

	/**
	 *
	 * @param array $files
	 * @return boolean
	 */
	public static function onUnitTestsList( &$files ) {
		$testDir = __DIR__ . '/tests/phpunit';
		$files = array_merge( $files, glob( "$testDir/*Test.php" ) );
		return true;
	}

	public static function onParserTestTables( &$tables ) {
		$tables[] = 'phptags_schemas';
		$tables[] = 'phptags_page_templates';
	}

}

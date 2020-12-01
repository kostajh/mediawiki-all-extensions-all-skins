<?php

use MediaWiki\MediaWikiServices;
use PhpTagsWiki\Extractor;

/**
 * PhpTags Wiki MediaWiki Hooks.
 *
 * @file PhpTagsWiki.hooks.php
 * @ingroup PhpTags
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class PhpTagsWikiHooks {

	/**
	 * Check on version compatibility
	 * @return boolean
	 * @throws MWException
	 */
	public static function onParserFirstCallInit() {
		if ( PHPTAGS_HOOK_RELEASE != 8 ) {
			throw new MWException( "\n\nThis version of the PhpTags Wiki extension is outdated and not compatible with current version of the PhpTags extension.\n Please update it." );
		}
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public static function onPhpTagsRuntimeFirstInit() {
		global $wgCacheEpoch;

		$version = ExtensionRegistry::getInstance()->getAllThings()['PhpTags Wiki']['version'];
		\PhpTags\Hooks::addJsonFile( __DIR__ . '/PhpTagsWiki.json', $version );
		\PhpTags\Hooks::addCallbackConstantValues( 'PhpTagsWikiHooks::initializeConstants', $version . $wgCacheEpoch );
		return true;
	}

	public static function initializeConstants() {
		// Add all defined namespace constants, which either
		// start with 'NS_' or have '_NS_' in their names
		$phpConstants = get_defined_constants( true );
		$nsConstants = [];
		foreach ( $phpConstants['user'] as $key => $value ) {
			if ( !is_int( $value ) ) {
				continue;
			}
			$pos = strpos( $key, 'NS_' );
			if ( $pos === false || ( $pos > 0 && $key[$pos - 1] !== '_' ) ) {
				continue;
			}
			$nsConstants[$key] = $value;
		}
		return $nsConstants;
	}

	/**
	 * @param LinksUpdate $linksUpdate
	 */
	public static function onLinksUpdate( LinksUpdate $linksUpdate ) {
		if ( !class_exists( 'TextExtracts\\ExtractFormatter' ) ) {
			return;
		}

		$config = MediaWikiServices::getInstance()->getMainConfig();
		try {
			if ( !$config->get( 'PhpTagsWikiExtract' ) ) {
				return;
			}
		} catch ( ConfigException $e ) {
			MWDebug::warning( $e->getMessage() );
			return;
		}

		$title = $linksUpdate->getTitle();
		if ( !$title->isContentPage() ) {
			return;
		}

		Extractor::update( $title );
	}

	/**
	 * @param Content $content
	 * @param Title $title
	 * @param ParserOutput $output
	 * @global User $wgUser
	 */
	public static function onContentAlterParserOutput( Content $content, Title $title, ParserOutput $output ) {
		if ( !$title->isContentPage() ) {
			return;
		}
		if ( !class_exists( 'TextExtracts\\ExtractFormatter' ) ) {
			return;
		}

		$config = MediaWikiServices::getInstance()->getMainConfig();
		try {
			if ( !$config->get( 'PhpTagsWikiExtract' ) || !$config->get( 'PhpTagsWikiExtractOnParserOutput' ) ) {
				return;
			}
		} catch ( ConfigException $e ) {
			MWDebug::warning( $e->getMessage() );
			return;
		}

		$pageId = $title->getArticleID();
		if ( !$pageId ) {
			return;
		}

		try {
			$db = wfGetDB( DB_REPLICA );
			$row = $db->selectRow(
				'phptagswiki_info',
				'ptw_page_id',
				[ 'ptw_page_id' => $pageId ],
				__METHOD__
			);
		} catch ( Exception $exception ) {
			MWDebug::warning( $exception->getMessage() );
			return;
		}

		if ( $row ) {
			return;
		}

		Extractor::update( $title );
	}

	/**
	 * This is attached to the MediaWiki 'LoadExtensionSchemaUpdates' hook.
	 * Fired when MediaWiki is updated to allow extensions to update the database
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'phptagswiki_info', __DIR__ . '/sql/info.sql' );
	}

}

<?php

class PXHooks {

	public static function describeDBSchema( DatabaseUpdater $updater ) {
		// DB updates.
		// For now, there's just a single SQL file for all DB types.
		$updater->addExtensionTable( 'px_packages', __DIR__ . "/../PageExchange.sql" );

		return true;
	}

	/**
	 * Called by the MediaWiki "ResourceLoaderSiteModulePages" hook.
	 */
	public static function loadJSPages( $skin, &$pages ) {
		$jsPages = [];

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'px_packages', 'pxp_js_pages', 'pxp_js_pages IS NOT NULL', __METHOD__ );
		while ( $row = $res->fetchRow() ) {
			$curJSPages = explode( ',', $row[0] );
			foreach ( $curJSPages as $curJSPage ) {
				$jsPages[] = $curJSPage;
			}
		}

		foreach ( $jsPages as $jsPage ) {
			$pages['MediaWiki:' . $jsPage] = [ 'type' => 'script' ];
		}
		return true;
	}

	/**
	 * Called by the MediaWiki "ResourceLoaderSiteStylesModulePages" hook.
	 */
	public static function loadCSSPages( $skin, &$pages ) {
		$cssPages = [];

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'px_packages', 'pxp_css_pages', 'pxp_css_pages IS NOT NULL', __METHOD__ );
		while ( $row = $res->fetchRow() ) {
			$curCSSPages = explode( ',', $row[0] );
			foreach ( $curCSSPages as $curCSSPage ) {
				$cssPages[] = $curCSSPage;
			}
		}
		foreach ( $cssPages as $cssPage ) {
			$pages['MediaWiki:' . $cssPage] = [ 'type' => 'style' ];
		}
		return true;
	}

	/**
	 * Implements AdminLinks hook from the Admin Links extension.
	 *
	 * @param ALTree &$adminLinksTree
	 * @return bool
	 */
	public static function addToAdminLinks( ALTree &$adminLinksTree ) {
		$generalSection = $adminLinksTree->getSection( wfMessage( 'adminlinks_general' )->text() );
		$extensionsRow = $generalSection->getRow( 'extensions' );

		if ( $extensionsRow === null ) {
			$extensionsRow = new ALRow( 'extensions' );
			$generalSection->addRow( $extensionsRow );
		}

		$extensionsRow->addItem( ALItem::newFromSpecialPage( 'Packages' ) );

		return true;
	}

}

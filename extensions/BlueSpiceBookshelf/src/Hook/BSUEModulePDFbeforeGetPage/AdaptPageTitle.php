<?php

namespace BlueSpice\Bookshelf\Hook\BSUEModulePDFbeforeGetPage;

use ConfigException;
use Exception;
use MediaWiki\MediaWikiServices;
use PageHierarchyProvider;
use Title;

class AdaptPageTitle {
	/**
	 * @param array &$params
	 * @return bool
	 * @throws ConfigException
	 */
	public static function callback( &$params ) {
		if ( !isset( $params['title'] ) ) {
			return true;
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		if ( !$config->get( 'BookshelfTitleDisplayText' ) ) {
			return true;
		}

		$oTitle = Title::newFromText( $params['title'] );
		if ( $oTitle == null ) {
			return true;
		}

		$sTitle = $oTitle->getPrefixedText();
		try {
			$oPHP = PageHierarchyProvider::getInstanceForArticle( $sTitle );
			$oEntry = $oPHP->getEntryFor( $sTitle );
			$sDisplayTitle = $sTitle;
			if ( $oEntry !== null ) {
				if ( isset( $oEntry->articleDisplayTitle ) ) {
					$sDisplayTitle = $oEntry->articleDisplayTitle;
				}
				// Fallback in case of no display title but subpage
				if ( str_replace( '_', ' ', $sDisplayTitle ) === $sTitle && $oTitle->isSubpage() ) {
					$sDisplayTitle = basename( $oTitle->getText() );
				}
			}

			$params['display-title'] = $sDisplayTitle;
		}
		catch ( Exception $e ) {
			return true;
		}

		return true;
	}
}

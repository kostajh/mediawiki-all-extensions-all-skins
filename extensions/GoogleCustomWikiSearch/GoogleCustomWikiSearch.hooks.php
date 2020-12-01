<?php

class GoogleCustomWikiSearchHooks {

	/**
	 * Possibly replace the built-in search
	 *
	 * @global boolean $wgGoogleCustomWikiSearchReplaceSearch
	 * @global boolean $wgDisableTextSearch
	 * @global string $wgSearchForwardUrl
	 * @return boolean
	 */
	public static function onSpecialSearchSetupEngine() {
		global $wgGoogleCustomWikiSearchReplaceSearch, $wgDisableTextSearch, $wgSearchForwardUrl;

		if ( !$wgGoogleCustomWikiSearchReplaceSearch ) {
			return true;
		}

		$wgDisableTextSearch = true;
		$specialPageTitle = SpecialPage::getTitleFor( 'GoogleCustomWikiSearch' );
		$wgSearchForwardUrl = $specialPageTitle->getFullURL( 'term=$1' );

		return true;
	}

	/**
	 * Possibly append Google search results to Special:Search
	 *
	 * @global boolean $wgGoogleCustomWikiSearchAppendToSearch
	 * @param SpecialPage $special
	 * @param string $par
	 * @return boolean
	 */
	public static function onSpecialPageAfterExecute( SpecialPage $special, $par ) {
		global $wgGoogleCustomWikiSearchAppendToSearch;

		// Only modify Special:Search
		if ( $special->getName() != 'Search' || !$wgGoogleCustomWikiSearchAppendToSearch ) {
			return true;
		}

		// Add gcws header to output
		$out = $special->getOutput();
		$out->addModuleStyles( 'ext.googleCustomWikiSearch' );
		$out->addHTML( Html::element( 'h1', array( 'id' => 'gcws_header' ),
				$special->msg( 'googlecustomwikisearch' )->text() ) );

		// Set up the Google search
		$googleCustomWikiSearch = new GoogleCustomWikiSearch( $special->getContext() );

		$request = $special->getRequest();
		$term = GoogleCustomWikiSearch::getSearchTerm( $request->getText( 'search' ), $par );
		$googleCustomWikiSearch->doSearch( $term );

		return true;
	}
}

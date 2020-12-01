<?php

class SpecialGoogleCustomWikiSearch extends SpecialPage {

	function __construct() {
		parent::__construct( 'GoogleCustomWikiSearch' );
	}

	/**
	 * Entry point
	 *
	 * @param string $par
	 */
	public function execute( $par ) {
		// Shamelessly borrowed from class SpecialSearch
		$this->setHeaders();
		$this->outputHeader();
		$out = $this->getOutput();
		$out->allowClickjacking();

		// Fetch the search term
		$request = $this->getRequest();
		$term = GoogleCustomWikiSearch::getSearchTerm( $request->getText( 'term' ), $par );

		$this->showResults( $term );
	}

	/**
	 * @param string $term
	 */
	public function showResults( $term ) {
		$this->setupPage( $term );

		$googleCustomWikiSearch = new GoogleCustomWikiSearch( $this->getContext() );
		$googleCustomWikiSearch->doSearch( $term );
	}

	/**
	 * @param string $term
	 */
	protected function setupPage( $term ) {
		$out = $this->getOutput();
		if ( strval( $term ) !== '' ) {
			$out->setPageTitle( $this->msg( 'searchresults' ) );
			$out->setHTMLTitle( $this->msg( 'pagetitle' )
					->plaintextParams( $this->msg( 'searchresults-title' )->plaintextParams( $term )->text() )
					->inContentLanguage()->text()
			);
		}
	}

	protected function getGroupName() {
		return 'redirects';
	}
}

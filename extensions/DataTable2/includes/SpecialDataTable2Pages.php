<?php

/**
 * @brief Special page DataTable2Pages for the @ref
 * Extensions-DataTable2.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 * @sa Largely inspired by SpecialListusers.php.
 */

/**
 * @brief Pager used in SpecialDataTable2Pages.
 *
 * @ingroup Extensions-DataTable2
 *
 * @sa [MediaWiki Manual:Pager.php]
 * (https://www.mediawiki.org/wiki/Manual:Pager.php)
 */
class DataTable2PagesPager extends DataTable2Pager {

	/* == public data members == */

	/** @brief Second parameter appended to the special page URL, or
	 * REQUEST variable 'pagename'.
	 */
	public $pagename;

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param IContextSource|null $context Context.
	 *
	 * @param string|null $par Parameters of the form
	 * *table*[//<i>page</i>] so that pages are selected for *table*
	 * and shown starting with *page*.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * special page <b>DataTable2Pages</b> accepts two parameters,
	 * which can either be appended to the URL
	 * (e.g. Special:DataTable2Pages/Employees//Kampala) or given as
	 * the REQUEST parameters <tt>tablename</tt> and
	 * <tt>pagename</tt>. The former take precedence. The separator
	 * between parameters appended to the URL is configured with the
	 * global variable @ref $wgSpecialDataTable2PageParSep. The page
	 * will display for the given table those pages whose titles are
	 * greater or equal to the given one (regardless of the namespace).
	 */
	public function __construct( IContextSource $context = null,
		$par = null ) {
		global $wgSpecialDataTable2PageParSep;

		$param = explode( $wgSpecialDataTable2PageParSep, $par, 2 );

		$this->pagename = isset( $param[1] ) && $param[1] != ''
			? $param[1] : $this->getRequest()->getText( 'pagename' );

		parent::__construct( $context, $param[0] );
	}

	/* == overriding methods == */

	/// Specify the database query to be run by AlphabeticPager.
	public function getQueryInfo() {
		global $wgDataTable2ReadSrc;

		$conds = [ 'dtd_table' => $this->tableDbKey,
			'dtd_page = page_id' ];

		$dbr = wfGetDB( DB_REPLICA );

		if ( $this->pagename != '' ) {
			$title = Title::newFromText( $this->pagename );

			$conds[] = 'page_title >='
				. $dbr->addQuotes( $title->getDBkey() );
		}

		return [
			'tables' => [ $wgDataTable2ReadSrc, 'page' ],
			'fields' => [ 'page_namespace', 'page_title',
				'records' => 'count(*)' ],
			'conds' => $conds,
			'options' => [ 'GROUP BY' => 'page_namespace, page_title' ]
		];
	}

	/// Specify `page_title` as the index field for AlphabeticPager.
	public function getIndexField() {
		return 'page_title';
	}

	/**
	 * @brief Format a data row.
	 *
	 * @param stdClass $row Database row object.
	 *
	 * @return string Wikitext.
	 */
	public function formatRow( $row ) {
		return $this->msg( 'datatable2pages-row',
			Title::makeTitle( $row->page_namespace,
				$row->page_title ), $this->tablename,
			$row->records )->text();
	}

	/**
	 * @brief Provide the page header, which contains a form to select data.
	 *
	 * @return string html code.
	 */
	public function getPageHeader() {
		$content = Html::rawElement( 'label',
			[ 'for' => 'pagename' ],
			$this->msg( 'datatable2pages-from' )->parse() ) . '&#160'
			. Xml::input( 'pagename', 25, $this->pagename,
				[ 'id' => 'pagename' ] ) . ' '
			. Html::rawElement( 'label',
				[ 'for' => 'tablename' ],
				$this->msg( 'datatable2pages-table' )->parse() ) . '&#160'
			. Xml::input( 'tablename', 25, $this->tablename,
				[ 'id' => 'tablename' ] );

		return $this->buildPageHeader( 'pages', $content );
	}

	// Re-implement IndexPager::getBody().
	public function getBody() {
		/** Return null if no table specified. */
		return $this->tablename ? parent::getBody() : null;
	}
}

/**
 * @brief Special page DataTable2Pages for the @ref
 * Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
class SpecialDataTable2Pages extends SpecialDataTable2 {
	public function __construct() {
		parent::__construct( 'DataTable2Pages' );
	}
}

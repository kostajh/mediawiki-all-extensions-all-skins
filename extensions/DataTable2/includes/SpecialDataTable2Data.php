<?php

/**
 * @brief Special page DataTable2Data for the @ref
 * Extensions-DataTable2.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 * @sa Largely inspired from SpecialListusers.php.
 */

/**
 * @brief Pager used in SpecialDataTable2Data.
 *
 * @ingroup Extensions-DataTable2
 *
 * @sa [MediaWiki Manual:Pager.php]
 * (https://www.mediawiki.org/wiki/Manual:Pager.php)
 */
class DataTable2DataPager extends DataTable2Pager {

	/* == public data members == */

	/** @brief Second parameter appended to the special page URL, or
	 * REQUEST variable 'pagename'.
	 */
	public $pagename;

	/** @brief Third parameter appended to the special page URL, or
	 * REQUEST variable 'data'.
	 */
	public $dataFrom;

	/* == private data members == */

	/// Whether the next row to format is the first one on the page.
	private $firstRow_ = true;

	private $columns_; ///< Column names.
	private $columnCount_; ///< count( @ref $columns_ ).

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param IContextSource|null $context Context.
	 *
	 * @param string|null $par Parameters of the form
	 * *table*[//<i>page</i>[//<i>data</i>]] so that data are selected
	 * for *table* and *page* starting at *data*.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * special page <b>DataTable2Data</b> accepts three parameters,
	 * which can either be appended to the URL
	 * (e.g. Special:DataTable2Pages/Employees//Kampala//Smith) or
	 * given as the REQUEST parameters <tt>tablename</tt>,
	 * <tt>pagename</tt> and <tt>data</tt>. The former take
	 * precedence. The separator between parameters appended to the
	 * URL is configured with the global variable @ref
	 * $wgSpecialDataTable2PageParSep. The page will display for the
	 * given table and page those records where the content of the
	 * first field is greater or equal to the given one.
	 */
	public function __construct( IContextSource $context = null,
		$par = null ) {
		global $wgSpecialDataTable2PageParSep;

		$param = explode( $wgSpecialDataTable2PageParSep, $par, 3 );

		$this->pagename = isset( $param[1] ) && $param[1] != ''
			? $param[1] :
			$this->getRequest()->getText( 'pagename' );

		$this->dataFrom = isset( $param[2] ) && $param[2] != ''
			? $param[2] :
			$this->getRequest()->getText( 'data' );

		parent::__construct( $context, $param[0] );

		/** Set @ref $columns_ from DataTable2Database::getColumns(). */
		$this->columns_ = DataTable2::singleton()->getDatabase()->getColumns(
			$this->tableDbKey );

		/** Cache count( @ref $columns_ ) in @ref $columnCount_ since
		 *	it is needed in formatRow().
		 */
		$this->columnCount_ = count( $this->columns_ );
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

			$conds['page_namespace'] = $title->getNamespace();
			$conds['page_title'] = $title->getDBkey();
		}

		if ( $this->dataFrom != '' ) {
			$conds[] = $this->getIndexField() . ' >= '
				. $dbr->addQuotes( $this->dataFrom );
		}

		return [
			'tables' => [ 'd' => $wgDataTable2ReadSrc, 'page' ],
			'fields' => [ 'page_namespace', 'page_title', 'd.*' ],
			'conds' => $conds
		];
	}

	/// Specify the first data column as the index field for AlphabeticPager.
	public function getIndexField() {
		return DataTable2Database::dataCol( 1 );
	}

	/**
	 * @brief Format a data row.
	 *
	 * @param stdClass $row Database row object.
	 *
	 * @return string Wikitext.
	 */
	public function formatRow( $row ) {
		$text = '';

		if ( $this->firstRow_ ) {
			global $wgSpecialDataTable2DataClasses;

			$classes = implode( ' ', $wgSpecialDataTable2DataClasses );
			$text .= "<table class='$classes'>\n<tr>\n";

			foreach ( $this->columns_ as $name ) {
				$text .= "<th>$name</th>\n";
			}

			$text .= "<th>{$this->msg( 'datatable2data-page-column-title' )->text()}</th>\n</tr>\n";

			$this->firstRow_ = false;
		}

		$text .= "<tr>\n";

		for ( $i = 1; $i <= $this->columnCount_; $i++ ) {
			$column = DataTable2Database::dataCol( $i );
			$text .= "<td>{$row->$column}</td>\n";
		}

		$text .= "<td>[[" . Title::makeTitle( $row->page_namespace,
			$row->page_title ) . "]]</td>\n";

		return $text . "</tr>\n";
	}

	/**
	 * @brief Provide wikitext to close the table.
	 *
	 * @return string Wikitext.
	 */
	public function getEndBody() {
	 /* Return an empty string if still at first row, i.e. the
	  * AlphabeticPager did not return any records. */
		return $this->firstRow_ ? '' : "</table>\n";
	}

	/**
	 * @brief Provide the page header, which contains a form to select data.
	 *
	 * @return string html code.
	 */
	public function getPageHeader() {
		$content = Html::rawElement( 'label',
			[ 'for' => 'data' ],
			$this->msg( 'datatable2data-from' )->parse() ) . '&#160'
			. Xml::input( 'data', 20, $this->dataFrom,
				[ 'id' => 'data' ] ) . ' '
			. Html::rawElement( 'label',
				[ 'for' => 'tablename' ],
				$this->msg( 'datatable2data-table' )->parse() ) . '&#160'
			. Xml::input( 'tablename', 20, $this->tablename,
				[ 'id' => 'tablename' ] ) . ' '
			. Html::rawElement( 'label',
				[ 'for' => 'pagename' ],
				$this->msg( 'datatable2data-page' )->parse() ) . '&#160'
			. Xml::input( 'pagename', 20, $this->pagename,
				[ 'id' => 'pagename' ] );

		return $this->buildPageHeader( 'data', $content );
	}
}

/**
 * @brief Special page DataTable2Data for the @ref
 * Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
class SpecialDataTable2Data extends SpecialDataTable2 {
	public function __construct() {
		parent::__construct( 'DataTable2Data' );
	}
}

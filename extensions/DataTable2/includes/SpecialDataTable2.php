<?php

/**
 * @brief Base classes for special pages for the @ref
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
 * @brief Pager base class for the @ref Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 *
 * @sa [MediaWiki Manual:Pager.php]
 * (https://www.mediawiki.org/wiki/Manual:Pager.php)
 */
abstract class DataTable2Pager extends AlphabeticPager {

	/* == public data members == */

	/** @brief First parameter appended to the special page URL, or
	 * REQUEST variable 'tablename'.
	 */
	public $tablename;
	public $tableDbKey; ///< @ref $tablename as db key.

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param IContextSource|null $context Context.
	 *
	 * @param string|null $tablename Logical table name.
	 */
	public function __construct( IContextSource $context = null,
		$tablename = null ) {
		if ( isset( $context ) ) {
			$this->setContext( $context );
		}

		$this->tablename = isset( $tablename ) && $tablename != ''
			? $tablename : $this->getRequest()->getText( 'tablename' );

		if ( $this->tablename == '' ) {
			$this->tablename = null;
		}

		if ( isset( $this->tablename ) ) {
			$this->tableDbKey = DataTable2Parser::table2title(
				$this->tablename )->getDBkey();
		}

		parent::__construct();
	}

	/* == operations == */

	/**
	 * @brief Provide the page header, which contains a form to select data.
	 *
	 * @param string $suffix Suffix of the special page in lowercase,
	 * such as `tables, pages, data`.
	 *
	 * @param string $content HTML code to put into the selection form.
	 *
	 * @return string html code.
	 *
	 * @sa [MediaWiki Manual:$wgScript]
	 * (https://www.mediawiki.org/wiki/$wgScript)
	 */
	public function buildPageHeader( $suffix, $content ) {
		global $wgScript;

		/** Include a hidden field with the page title, needed as a
		 *	GET parameter to index.php.
		 */
		list( $title ) = explode( '/',
			$this->getTitle()->getPrefixedDBkey(), 2 );

		return Xml::openElement( 'form',
			[ 'method' => 'get',
				'action' => $wgScript,
				'id' => "mw-datatable2$suffix-form" ] )
			. Xml::fieldset( $this->msg( "datatable2$suffix-legend" )->text() )
			. Html::hidden( 'title', $title )
			. Html::hidden( 'limit', $this->mLimit )
			. $content . ' '
			. Xml::submitButton( $this->msg( 'allpagessubmit' )->text() )
			. Xml::closeElement( 'fieldset' )
			. Xml::closeElement( 'form' );
	}
}

/**
 * @brief Special page base class for the @ref
 * Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
abstract class SpecialDataTable2 extends IncludableSpecialPage {
	/* == private data members == */

	/// Whether this page needs a tablename to display data.
	private $needsTablename_;

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param string $name Name of the special page, as seen in links
	 * and URLs.
	 *
	 * @param bool $needsTablename Whether this page needs a
	 * tablename to display data.
	 */
	public function __construct( $name, $needsTablename = true ) {
		parent::__construct( $name, 'datatable2-specialpages' );

		$this->needsTablename_ = $needsTablename;
	}

	/* == operations == */

	/**
	 * @brief Execute the special page.
	 *
	 * @param string $par Parameter passed to the pager class.
	 */
	public function execute( $par ) {
		if ( !$this->including()
			&& !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();
		$this->outputHeader();

		/** Create an instance of a pager class, whose name is
		 * obtained by appending 'Pager' to the name of the
		 * special page.
		 */
		$pagerClass = "{$this->getName()}Pager";
		$pager = new $pagerClass( $this->getContext(), $par );

		$html = '';

		if ( !$this->including() ) {
			$html .= $pager->getPageHeader();
		}

		$body = $pager->getBody();

		if ( $body ) {
			$html .= $pager->getNavigationBar()
				. DataTable2::sandboxParse( $body, $this->getUser() )
				. $pager->getNavigationBar();
		} elseif ( isset( $pager->tablename )
			|| !$this->needsTablename_ ) {
			/** Show a "no data found" message if no data were
			 *	found and either a table was specified, or the
			 *	page could display data even without a table
			 *	name.
			 */
			$html .= $this->msg( strtolower( $this->getName() )
				. '-noresult' )->parseAsBlock();
		}

		$this->getOutput()->addHTML( $html );
	}
}

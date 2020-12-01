<?php
/**
 * Special page to direct the user to a random page in specified category
 *
 * @file
 * @ingroup SpecialPage
 * @author VasilievVV <vasilvv@gmail.com>, based on SpecialRandompage.php code
 * @license GPL-2.0-or-later
 */
class RandomPageInCategory extends RandomPage {
	private $category = null;

	public function __construct() {
		parent::__construct();
		$this->mName = 'RandomInCategory';
	}

	public function execute( $par ) {
		$out = $this->getOutput();

		$this->setHeaders();
		if ( $par === null ) {
			$requestCategory = $this->getRequest()->getVal( 'category' );
			if ( $requestCategory ) {
				$par = $requestCategory;
			} else {
				$out->addHTML( $this->getForm() );
				return;
			}
		}

		if ( !$this->setCategory( $par ) ) {
			$out->addHTML( $this->getForm( $par ) );
			return;
		}

		$title = $this->getRandomTitle();

		if ( $title === null ) {
			$out->addWikiMsg( 'randomincategory-nocategory', $par );
			$out->addHTML( $this->getForm( $par ) );
			return;
		}

		$out->redirect( $title->getFullURL() );
	}

	public function getCategory() {
		return $this->category;
	}

	public function setCategory( $cat ) {
		$category = Title::makeTitleSafe( NS_CATEGORY, $cat );
		// Invalid title
		if ( !$category ) {
			return false;
		}
		$this->category = $category->getDBkey();
		return true;
	}

	protected function getQueryInfo( $randstr ) {
		$query = parent::getQueryInfo( $randstr );

		$query['tables'][] = 'categorylinks';

		unset( $query['conds']['page_namespace'] );
		array_merge( $query['conds'], [ 'page_namespace != ' . NS_CATEGORY ] );

		$query['conds']['cl_to'] = $this->category;

		// FIXME: FORCE INDEX gets added in wrong place, goes after table join, should be before
		// bug 27081
		unset( $query['options']['USE INDEX'] );

		$query['join_conds'] = [
			'categorylinks' => [
				'JOIN', [ 'page_id=cl_from' ]
			]
		];

		return $query;
	}

	public function getForm( $par = null ) {
		$category = $par ?? $this->getRequest()->getVal( 'category' );

		return Xml::openElement( 'form', [
				'method' => 'get',
				'action' => $this->getConfig()->get( 'Script' )
			] ) .
				Xml::openElement( 'fieldset' ) .
					Xml::element( 'legend', [], $this->msg( 'randomincategory' )->text() ) .
					Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
					Xml::openElement( 'p' ) .
						Xml::label( $this->msg( 'randomincategory-label' )->text(), 'category' ) . ' ' .
						Xml::input( 'category', null, $category, [ 'id' => 'category' ] ) . ' ' .
						Xml::submitButton( $this->msg( 'randomincategory-submit' )->text() ) .
					Xml::closeElement( 'p' ) .
				Xml::closeElement( 'fieldset' ) .
			Xml::closeElement( 'form' );
	}
}

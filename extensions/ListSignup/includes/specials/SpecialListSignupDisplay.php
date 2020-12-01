<?php
/**
 * Implements SpecialListSignupDisplay - hints from SpecialListFiles
 *
 * @file
 * @ingroup SpecialPage
 */
class SpecialListSignupDisplay extends SpecialPage {

	public function __construct() {
		parent::__construct( 'ListSignupDisplay' );
	}

	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		if ( !$this->getUser()->isAllowed( 'viewlistsignupdisplay' ) ) {
			throw new PermissionsError( 'viewlistsignupdisplay' );
		}

		$pager = new ListSignupPager(
			$this->getContext()
		);

		$pager->getForm();
		$body = $pager->getBody();
		$nav = $pager->getNavigationBar();
		$html = "$body<br>\n$nav";

		$this->getOutput()->addHTML( $html . Linker::specialLink( 'ListSignupPurge',
				'listsignuppurge-legend' ) );
	}
}

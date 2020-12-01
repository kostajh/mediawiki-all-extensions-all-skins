<?php

namespace BlueSpice\PageFormsConnector\SaferEdit\EnvironmentChecker;

use BlueSpice\SaferEdit\EnvironmentChecker\Base;
use Title;

class FormEdit extends Base {

	/**
	 *
	 * @param bool &$result
	 * @return bool
	 */
	public function isEditMode( &$result ) {
		$action = $this->context->getRequest()->getText( 'action', 'view' );

		if ( $action === 'formedit' && $this->userCanEdit() ) {
			$result = true;
			return false;
		}

		// When editing a page over Special:FormEdit
		if ( $this->getTitleEditedOverSP() instanceof Title ) {
			$result = true;
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param bool &$result
	 * @return bool
	 */
	public function shouldShowWarning( &$result ) {
		$isEdit = false;
		$this->isEditMode( $isEdit );
		if ( $isEdit ) {
			$result = false;
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param Title|null $title
	 * @return Title
	 */
	private function getTitleEditedOverSP( $title = null ) {
		if ( $title === null ) {
			$title = $this->context->getTitle();
		}
		if ( $title->isSpecialPage() ) {
			$dbKey = $title->getDBkey();
			$bits = explode( '/', $dbKey );
			$specialPageName = array_shift( $bits );
			if ( $specialPageName ) {
				$specialTitle = \MediaWiki\MediaWikiServices::getInstance()
					->getSpecialPageFactory()
					->getTitleForAlias( $specialPageName );
				if ( !$specialTitle instanceof Title
					|| !$specialTitle->equals( \SpecialPage::getTitleFor( 'FormEdit' ) ) ) {
					return null;
				}
				$page = array_pop( $bits );
				$pageTitle = Title::newFromText( $page );
				if ( !$pageTitle instanceof Title || !$pageTitle->exists() ) {
					return null;
				}
				return $pageTitle;
			}
		}
		return null;
	}

	/**
	 * Modify Title object that is being edited
	 *
	 * @param Title &$title
	 * @return bool
	 */
	public function getEditedTitle( &$title ) {
		$spTitle = $this->getTitleEditedOverSP( $title );
		if ( $spTitle instanceof Title ) {
			$title = $spTitle;
		}
		return true;
	}
}

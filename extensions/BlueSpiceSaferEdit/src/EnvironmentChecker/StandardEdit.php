<?php

namespace BlueSpice\SaferEdit\EnvironmentChecker;

class StandardEdit extends Base {

	/**
	 * @inheritDoc
	 */
	public function isEditMode( &$result ) {
		$action = $this->context->getRequest()->getText( 'action', 'view' );

		if ( ( $action === 'edit' || $action === 'submit' ) && $this->userCanEdit() ) {
			$result = true;
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldShowWarning( &$result ) {
		$isEdit = false;
		$this->isEditMode( $isEdit );
		if ( $isEdit ) {
			$result = false;
			return false;
		}

		if ( $this->context->getTitle()->isContentPage() ) {
			$result = true;
		}
		return true;
	}
}

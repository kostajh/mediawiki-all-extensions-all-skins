<?php

namespace BlueSpice\VisualEditorConnector\SaferEdit\EnvironmentChecker;

use BlueSpice\SaferEdit\EnvironmentChecker\Base;

class VeActionChecker extends Base {

	/**
	 *
	 * @param bool &$result
	 * @return bool
	 */
	public function isEditMode( &$result ) {
		$veAction = $this->context->getRequest()->getText( 'veaction', '' );

		if ( $veAction !== '' && $this->userCanEdit() ) {
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
}

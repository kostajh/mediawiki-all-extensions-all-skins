<?php

namespace BlueSpice\VisualEditorConnector\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	/**
	 * Skip if not in visual edit mode or Extension disabled
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->getConfig()->get( 'VisualEditorConnectorEnableVisualEditor' ) ) {
			return true;
		}
		$action = $this->getContext()->getRequest()->getVal(
			'action',
			$this->getContext()->getRequest()->getVal( 'veaction', 'view' )
		);

		if ( $action === 'edit' || $action === 'editsource' ) {
			return false;
		}

		return true;
	}

	protected function doProcess() {
		$this->out->addModules( [
			"ext.bluespice.visualEditorConnector",
		] );
	}

}

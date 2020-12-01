<?php

namespace BlueSpice\ReadConfirmation\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddManagePageAssignmentsIntegration extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->out->getTitle()->isSpecial( 'ManagePageAssignments' ) ) {
			return false;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModuleStyles(
			'ext.readconfirmation.pageassignmentsintegration.styles'
		);
		$this->out->addModules( 'ext.readconfirmation.pageassignmentsintegration' );
		return true;
	}

}

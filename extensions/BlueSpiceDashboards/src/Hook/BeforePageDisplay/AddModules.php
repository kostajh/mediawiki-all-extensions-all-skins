<?php

namespace BlueSpice\Dashboards\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return !$this->out->getUser()->getOption(
			'bs-dashboards-pref-userdashboardonlogo',
			false
		);
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.dashboards' );
		return true;
	}

}

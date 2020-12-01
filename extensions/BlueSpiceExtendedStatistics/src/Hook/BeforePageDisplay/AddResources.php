<?php

namespace BlueSpice\ExtendedStatistics\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.statistics.styles' );

		return true;
	}

}

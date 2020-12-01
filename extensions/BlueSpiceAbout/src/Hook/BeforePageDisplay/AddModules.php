<?php

namespace BlueSpice\About\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.bluespiceabout' );
		return true;
	}

}

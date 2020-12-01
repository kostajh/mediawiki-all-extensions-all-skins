<?php

namespace BlueSpice\InsertMagic\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.insertMagic.styles' );

		return true;
	}
}

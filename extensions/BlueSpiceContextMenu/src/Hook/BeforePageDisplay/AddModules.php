<?php

namespace BlueSpice\ContextMenu\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addmodules( 'ext.bluespice.contextmenu' );
		return true;
	}

}

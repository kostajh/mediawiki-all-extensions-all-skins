<?php

namespace BlueSpice\UserSidebar\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddStyles extends BeforePageDisplay {

	protected function skipProcessing() {
		return $this->getContext()->getUser()->isAnon();
	}

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.blueSpiceUserSidebar.styles' );
		return true;
	}
}

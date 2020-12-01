<?php

namespace BlueSpice\Social\Topics\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aScripts[] = "ext.bluespice.social.creatediscussion";
		return true;
	}
}

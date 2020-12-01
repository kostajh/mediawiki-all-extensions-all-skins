<?php

namespace BlueSpice\Social\Resolve\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aVarMsgKeys['resolved'] = 'bs-socialresolve-var-resolved';
		$this->aScripts[] = "ext.bluespice.social.resolve";
		$this->aStyles[] = "ext.bluespice.social.resolve.styles";
		return true;
	}
}

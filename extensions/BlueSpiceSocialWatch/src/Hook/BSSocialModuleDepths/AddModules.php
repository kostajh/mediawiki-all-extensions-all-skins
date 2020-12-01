<?php

namespace BlueSpice\Social\Watch\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aVarMsgKeys['watch'] = 'bs-socialwatch-var-watch';
		$this->aScripts[] = "ext.bluespice.social.watch";
		$this->aStyles[] = "ext.bluespice.social.watch.styles";

		return true;
	}
}

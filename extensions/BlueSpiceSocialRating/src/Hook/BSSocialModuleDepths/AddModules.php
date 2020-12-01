<?php

namespace BlueSpice\Social\Rating\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aVarMsgKeys['ratingcount'] = 'bs-socialrating-var-rating';

		return true;
	}
}

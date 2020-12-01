<?php

namespace BlueSpice\Social\Tags\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aVarMsgKeys['tags'] = 'bs-socialtags-var-tags';
		$this->aScripts[] = "ext.bluespice.social.tags";
		$this->aStyles[] = "ext.bluespice.social.tags.styles";

		return true;
	}
}

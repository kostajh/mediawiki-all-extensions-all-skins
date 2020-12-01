<?php

namespace BlueSpice\Social\WikiPage\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aScripts[] = 'ext.bluespice.social.createwikipageentity';
		$this->aScripts[] = 'ext.bluespice.social.createfirstattachment';
		$this->aStyles[] = 'ext.bluespice.social.articles.styles';

		return true;
	}
}

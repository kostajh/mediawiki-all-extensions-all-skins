<?php

namespace BlueSpice\Social\TimelineUpdate\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddModules extends BSSocialModuleDepths {

	protected function doProcess() {
		$this->aScripts[] = 'ext.bluespice.social.timelineupdate';
		$this->aStyles[] = 'ext.bluespice.social.timelineupdate.styles';

		return true;
	}
}

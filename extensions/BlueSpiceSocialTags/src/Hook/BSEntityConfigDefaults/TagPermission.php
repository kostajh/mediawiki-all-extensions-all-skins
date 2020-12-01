<?php

namespace BlueSpice\Social\Tags\Hook\BSEntityConfigDefaults;

use BlueSpice\Hook\BSEntityConfigDefaults;

class TagPermission extends BSEntityConfigDefaults {

	protected function doProcess() {
		$this->defaultSettings['TagPermission'] = 'social-tagging';
		return true;
	}
}

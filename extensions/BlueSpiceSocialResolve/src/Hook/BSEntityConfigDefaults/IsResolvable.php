<?php

namespace BlueSpice\Social\Resolve\Hook\BSEntityConfigDefaults;

use BlueSpice\Hook\BSEntityConfigDefaults;

class IsResolvable extends BSEntityConfigDefaults {

	protected function doProcess() {
		$this->defaultSettings['IsResolvable'] = false;
		$this->defaultSettings['ResolvePermission'] = 'edit';
		return true;
	}
}

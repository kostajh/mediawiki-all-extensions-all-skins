<?php

namespace BlueSpice\Social\Watch\Hook\BSEntityConfigDefaults;

use BlueSpice\Hook\BSEntityConfigDefaults;

class IsWatchable extends BSEntityConfigDefaults {

	protected function doProcess() {
		$this->defaultSettings['IsWatchable'] = true;
		return true;
	}
}

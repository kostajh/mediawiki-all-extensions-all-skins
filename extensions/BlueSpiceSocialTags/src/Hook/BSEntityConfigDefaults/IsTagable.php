<?php

namespace BlueSpice\Social\Tags\Hook\BSEntityConfigDefaults;

use BlueSpice\Hook\BSEntityConfigDefaults;

class IsTagable extends BSEntityConfigDefaults {

	protected function doProcess() {
		$this->defaultSettings['IsTagable'] = true;
		return true;
	}
}

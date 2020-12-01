<?php

namespace BlueSpice\Social\Rating\Hook\BSEntityConfigDefaults;

use BlueSpice\Hook\BSEntityConfigDefaults;

class IsRateable extends BSEntityConfigDefaults {

	protected function doProcess() {
		$this->defaultSettings['IsRateable'] = true;
		return true;
	}
}

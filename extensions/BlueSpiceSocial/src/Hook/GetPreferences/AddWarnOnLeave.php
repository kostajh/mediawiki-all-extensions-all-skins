<?php

namespace BlueSpice\Social\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddWarnOnLeave extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-social-warnonleave'] = [
			'type' => 'check',
			'label-message' => 'bs-social-pref-warnonleave',
			'section' => 'editing/social',
		];
		return true;
	}
}

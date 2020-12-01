<?php

namespace BlueSpice\Social\Topics\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddSkipCreateDialoge extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-social-topics-skipcreatedialog'] = [
			'type' => 'check',
			'label-message' => 'bs-socialtopics-pref-skipcreatedialog',
			'section' => 'editing/social',
		];
		return true;
	}
}

<?php

namespace BlueSpice\Dashboards\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddUserDashboardOnLogo extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-dashboards-pref-userdashboardonlogo'] = [
			'type' => 'toggle',
			'label-message' => 'bs-dashboards-pref-userdashboardonlogo',
			'section' => 'rendering/dashboards',
		];
		return true;
	}
}

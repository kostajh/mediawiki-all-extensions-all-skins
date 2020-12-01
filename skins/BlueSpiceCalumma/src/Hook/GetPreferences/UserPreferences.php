<?php
namespace BlueSpice\Calumma\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class UserPreferences extends GetPreferences {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		/* navigation-main */
		$this->preferences['bs-calumma-settings-navigation-main-collapse'] = [
			'section' => 'rendering/bs-calumma',
			'label-message' => 'bs-calumma-pref-toggle-navigaiton-main',
			'type' => 'toggle'
		];

		/* sitetools-main */
		$this->preferences['bs-calumma-settings-sitetools-main-collapse'] = [
			'section' => 'rendering/bs-calumma',
			'label-message' => 'bs-calumma-pref-toggle-sitetools-main',
			'type' => 'toggle'
		];

		return true;
	}
}

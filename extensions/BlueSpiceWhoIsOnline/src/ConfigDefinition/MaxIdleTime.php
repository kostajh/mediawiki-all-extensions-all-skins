<?php

namespace BlueSpice\WhoIsOnline\ConfigDefinition;

use BlueSpice\ConfigDefinition\IntSetting;

class MaxIdleTime extends IntSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_ADMINISTRATION . '/BlueSpiceWhoIsOnline',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceWhoIsOnline/' . static::FEATURE_ADMINISTRATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceWhoIsOnline',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-whoisonline-pref-maxidletime';
	}
}

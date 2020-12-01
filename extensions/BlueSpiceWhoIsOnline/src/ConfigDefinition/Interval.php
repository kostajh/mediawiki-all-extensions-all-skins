<?php

namespace BlueSpice\WhoIsOnline\ConfigDefinition;

use BlueSpice\ConfigDefinition\IntSetting;

class Interval extends IntSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SYSTEM . '/BlueSpiceWhoIsOnline',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceWhoIsOnline/' . static::FEATURE_SYSTEM,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceWhoIsOnline',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-whoisonline-pref-interval';
	}

	/**
	 *
	 * @return bool
	 */
	public function isRLConfigVar() {
		return true;
	}
}

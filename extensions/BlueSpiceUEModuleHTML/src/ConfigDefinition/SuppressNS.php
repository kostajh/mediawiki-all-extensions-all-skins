<?php

namespace BlueSpice\UEModuleHTML\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class SuppressNS extends BooleanSetting {

	/**
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceUEModuleHTML',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceUEModuleHTML/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceUEModuleHTML',
		];
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-uemodulehtml-pref-SuppressNS';
	}
}

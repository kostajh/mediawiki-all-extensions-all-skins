<?php

namespace BlueSpice\UEModuleDOCX\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class SuppressNS extends BooleanSetting {

	/**
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceUEModuleDOCX',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceUEModuleDOCX/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceUEModuleDOCX',
		];
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-uemoduledocx-pref-SuppressNS';
	}
}

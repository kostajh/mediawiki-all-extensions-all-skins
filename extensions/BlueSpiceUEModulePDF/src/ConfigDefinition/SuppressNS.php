<?php

namespace BlueSpice\UEModulePDF\ConfigDefinition;

class SuppressNS extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceUEModulePDF',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceUEModulePDF/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceUEModulePDF',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-uemodulepdf-pref-suppressns';
	}
}

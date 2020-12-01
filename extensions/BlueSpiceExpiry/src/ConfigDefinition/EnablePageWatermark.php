<?php

namespace BlueSpice\Expiry\ConfigDefinition;

class EnablePageWatermark extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_QUALITY_ASSURANCE . '/BlueSpiceExpiry',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceExpiry/' . static::FEATURE_QUALITY_ASSURANCE,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceExpiry',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-expiry-pref-enablepagewatermark';
	}

}

<?php

namespace BlueSpice\ExtendedStatistics\ConfigDefinition;

class MaxNumberOfIntervals extends \BlueSpice\ConfigDefinition\IntSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_DATA_ANALYSIS . '/BlueSpiceExtendedStatistics',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceExtendedStatistics/' . static::FEATURE_DATA_ANALYSIS ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceExtendedStatistics',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-statistics-pref-maxnumberofintervals';
	}

}

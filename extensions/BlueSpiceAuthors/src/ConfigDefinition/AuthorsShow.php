<?php

namespace BlueSpice\Authors\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class AuthorsShow extends BooleanSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_DATA_ANALYSIS . '/BlueSpiceAuthors',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceAuthors/' . static::FEATURE_DATA_ANALYSIS ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceAuthors',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-authors-pref-show';
	}
}

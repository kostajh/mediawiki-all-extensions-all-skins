<?php

namespace BlueSpice\Bookshelf\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class TitleDisplayText extends BooleanSetting implements \BlueSpice\Bookshelf\ISettingPaths {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_BOOK . '/BlueSpiceBookshelf',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceBookshelf/' . static::FEATURE_BOOK,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceBookshelf',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-bookshelf-pref-TitleDisplayText';
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-bookshelf-pref-titledisplaytext-help';
	}
}

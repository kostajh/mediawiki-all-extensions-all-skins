<?php

namespace BlueSpice\Bookshelf\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class PrependPageTOCNumbers extends BooleanSetting implements \BlueSpice\Bookshelf\ISettingPaths {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_CONTENT_STRUCTURING . '/BlueSpiceBookshelf',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceBookshelf/' . static::FEATURE_CONTENT_STRUCTURING ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceBookshelf',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-bookshelf-pref-PrependPageTOCNumbers';
	}
}

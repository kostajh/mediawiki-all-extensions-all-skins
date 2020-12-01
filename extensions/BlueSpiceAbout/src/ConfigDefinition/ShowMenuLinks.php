<?php

namespace BlueSpice\About\ConfigDefinition;

class ShowMenuLinks extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SEARCH . '/BlueSpiceAbout',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceAbout/' . static::FEATURE_SEARCH ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceAbout',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-bluespiceabout-show-menu-links';
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-bluespiceabout-pref-showmenulinks-help';
	}
}

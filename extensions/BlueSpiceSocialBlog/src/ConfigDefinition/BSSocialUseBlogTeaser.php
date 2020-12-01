<?php

namespace BlueSpice\Social\Blog\ConfigDefinition;

class BSSocialUseBlogTeaser extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_PERSONALISATION . '/BlueSpiceSocialBlog',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceSocialBlog/' . static::FEATURE_PERSONALISATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceSocialBlog',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-socialblog-toc-useblogteaser';
	}

	/**
	 *
	 * @return bool
	 */
	public function isRLConfigVar() {
		return true;
	}

}

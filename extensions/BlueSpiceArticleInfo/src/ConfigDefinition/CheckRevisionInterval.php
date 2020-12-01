<?php

namespace BlueSpice\ArticleInfo\ConfigDefinition;

class CheckRevisionInterval extends \BlueSpice\ConfigDefinition\IntSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_DATA_ANALYSIS . '/BlueSpiceArticleInfo',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceArticleInfo/' . static::FEATURE_DATA_ANALYSIS ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceArticleInfo',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-articleinfo-pref-CheckRevisionInterval';
	}

	/**
	 *
	 * @return bool
	 */
	public function isRLConfigVar() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-articleinfo-pref-checkrevisioninterval-help';
	}
}

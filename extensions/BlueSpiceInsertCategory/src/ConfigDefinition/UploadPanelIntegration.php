<?php

namespace BlueSpice\InsertCategory\ConfigDefinition;

class UploadPanelIntegration extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		$ext = 'BlueSpiceInsertCategory';
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_CONTENT_STRUCTURING . "/$ext",
			static::MAIN_PATH_EXTENSION . "/$ext/" . static::FEATURE_CONTENT_STRUCTURING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . "/$ext",
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-insertcategory-pref-uploadpanelintegration';
	}

	/**
	 *
	 * @return bool
	 */
	public function isRLConfigVar() {
		return true;
	}

}

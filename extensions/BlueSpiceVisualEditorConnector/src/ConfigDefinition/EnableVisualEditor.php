<?php

namespace BlueSpice\VisualEditorConnector\ConfigDefinition;

class EnableVisualEditor extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EDITOR . '/BlueSpiceVisualEditorConnector',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceVisualEditorConnector/' . static::FEATURE_EDITOR ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceVisualEditorConnector',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-visualeditorconnector-enable-visualeditor';
	}

	/**
	 *
	 * @return bool
	 */
	public function isRLConfigVar() {
		return true;
	}
}

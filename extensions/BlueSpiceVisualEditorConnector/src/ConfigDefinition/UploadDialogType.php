<?php

namespace BlueSpice\VisualEditorConnector\ConfigDefinition;

use BlueSpice\ConfigDefinition;

use HTMLSelectField;

class UploadDialogType extends ConfigDefinition {

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
	 * @return \HTMLFormField
	 */
	public function getHtmlFormField() {
		return new HTMLSelectField( $this->makeFormFieldParams() );
	}

	protected function makeFormFieldParams() {
		$params = parent::makeFormFieldParams();
		$params['options-messages'] = [
			'bs-visualeditorconnector-upload-type-original' => 'original',
			'bs-visualeditorconnector-upload-type-simple' => 'simple',
			'bs-visualeditorconnector-upload-type-one-click' => 'one-click',
		];
		$params['options'] = [
			'original' => 'original',
			'simple' => 'simple',
			'one-click' => 'one-click',
		];
		return $params;
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-visualeditorconnector-upload-type';
	}

}

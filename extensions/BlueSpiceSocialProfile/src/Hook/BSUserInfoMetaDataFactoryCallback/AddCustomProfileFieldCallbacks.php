<?php
namespace BlueSpice\Social\Profile\Hook\BSUserInfoMetaDataFactoryCallback;

use BlueSpice\UserInfo\Hook\BSUserInfoMetaDataFactoryCallback;

class AddCustomProfileFieldCallbacks extends BSUserInfoMetaDataFactoryCallback {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->callback ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		$field = $factory->factory( $this->name, $this->user );
		if ( !$field ) {
			return true;
		}
		if ( $field->isHidden() ) {
			return true;
		}

		$this->callback = "\\BlueSpice\\Social\\Profile\\MetaData\\CustomField::getInstance";
		return true;
	}
}

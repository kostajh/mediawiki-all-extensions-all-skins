<?php
namespace BlueSpice\Social\Profile\Hook\BSUserInfoMetaDataFactoryAllKeys;

use BlueSpice\Social\Profile\Field;
use BlueSpice\UserInfo\Hook\BSUserInfoMetaDataFactoryAllKeys;

class AddCustomProfileFieldKeys extends BSUserInfoMetaDataFactoryAllKeys {

	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);

		foreach ( $factory->getFieldDefinitions() as $field => $definition ) {
			if ( isset( $definition[Field::KEY_HIDDEN] ) && $definition[Field::KEY_HIDDEN] === true ) {
				continue;
			}
			$this->keys[] = $field;
		}
		return true;
	}
}

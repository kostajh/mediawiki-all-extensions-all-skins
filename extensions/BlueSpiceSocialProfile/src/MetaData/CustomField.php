<?php
namespace BlueSpice\Social\Profile\MetaData;

use BlueSpice\Social\Profile\ICustomField;
use BlueSpice\UserInfo\MetaData;
use MediaWiki\MediaWikiServices;

class CustomField extends MetaData {

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->getProfileCustomField()->getLabel();
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->getProfileCustomField()->getValue();
	}

	/**
	 *
	 * @return ICustomField
	 */
	protected function getProfileCustomField() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		return $factory->factory( $this->name, $this->user );
	}

}

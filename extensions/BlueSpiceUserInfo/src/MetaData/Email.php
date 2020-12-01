<?php
namespace BlueSpice\UserInfo\MetaData;

use BlueSpice\UserInfo\MetaData;
use Message;

class Email extends MetaData {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		return Message::newFromKey(
			"bs-userinfo-metadata-email"
		)->plain();
	}

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->user->getEmail();
	}

	/**
	 *
	 * @return bool
	 */
	public function isSubTitle() {
		return true;
	}

}

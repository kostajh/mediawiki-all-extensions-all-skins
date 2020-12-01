<?php

namespace BlueSpice\UserInfo\MetaData;

use BlueSpice\UserInfo\MetaData;
use MediaWiki\MediaWikiServices;
use Message;

class Name extends MetaData {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		return Message::newFromKey(
			"bs-userinfo-metadata-name"
		)->plain();
	}

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		$userHelper = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getUserHelper( $this->user );

		return $userHelper->getDisplayName();
	}

	/**
	 *
	 * @return bool
	 */
	public function isTitle() {
		return true;
	}

}

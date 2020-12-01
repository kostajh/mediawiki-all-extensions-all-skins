<?php

namespace BlueSpice\Expiry\Data;

use MediaWiki\MediaWikiServices;

class Store implements \BlueSpice\Data\IStore {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer(
			$this->getReader(),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}

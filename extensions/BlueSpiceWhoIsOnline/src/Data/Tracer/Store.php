<?php

namespace BlueSpice\WhoIsOnline\Data\Tracer;

use BlueSpice\WhoIsOnline\Data\Writer;
use MediaWiki\MediaWikiServices;

class Store extends \BlueSpice\WhoIsOnline\Data\Store {

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

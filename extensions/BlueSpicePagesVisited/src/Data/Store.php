<?php

namespace BlueSpice\PagesVisited\Data;

use BlueSpice\Data\NoWriterException;
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
	 * @throws NoWriterException
	 */
	public function getWriter() {
		throw new NoWriterException();
	}
}

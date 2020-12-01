<?php

namespace BlueSpice\EchoConnector\Data\Watchlist;

use BlueSpice\Data\Watchlist\Store as BaseStore;
use MediaWiki\MediaWikiServices;

class Store extends BaseStore {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			MediaWikiServices::getInstance()->getDBLoadBalancer(),
			$this->context
		);
	}
}

<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use IContextSource;
use MediaWiki\MediaWikiServices;

class Store implements \BlueSpice\Data\IStore, \BlueSpice\Data\Entity\IStore {

	/**
	 * @param IContextSource|null $context
	 * @return Reader
	 */
	public function getReader( IContextSource $context = null ) {
		return new Reader(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 * @param IContextSource|null $context
	 * @return Writer
	 */
	public function getWriter( IContextSource $context = null ) {
		return new Writer(
			$this->getReader(),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}

<?php
namespace BlueSpice\NamespaceCSS\Api\Store;

use BlueSpice\Context;
use BlueSpice\NamespaceCSS\Data\Store;
use MediaWiki\MediaWikiServices;

class NamespaceCSS extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}

<?php
namespace BlueSpice\Readers\Api\Store;

use BlueSpice\Context;
use BlueSpice\Readers\Data\PageReaders\Store;

class PageReaders extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->getServices()->getDBLoadBalancer()
		);
	}
}

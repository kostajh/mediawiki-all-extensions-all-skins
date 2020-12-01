<?php

namespace BlueSpice\Authors\Api\Store;

use BlueSpice\Api\Store as StoreBase;
use BlueSpice\Authors\Data\PageAuthors\Store;

class PageAuthors extends StoreBase {

	/**
	 *
	 * @return Store
	 */
	protected function makeDataStore() {
		return new Store( $this->getContext(), $this->getConfig() );
	}
}

<?php

namespace BlueSpice\Social\Api\Store;

class Entity extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new \BlueSpice\Social\Data\Entity\Store( $this->getContext() );
	}
}

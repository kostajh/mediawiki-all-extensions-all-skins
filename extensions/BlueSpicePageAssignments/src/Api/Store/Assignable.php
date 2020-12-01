<?php
namespace BlueSpice\PageAssignments\Api\Store;

use BlueSpice\Context;
use BlueSpice\PageAssignments\Data\Assignable\Store;

class Assignable extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->getServices()->getDBLoadBalancer()
		);
	}
}

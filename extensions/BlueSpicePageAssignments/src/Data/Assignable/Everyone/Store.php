<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Everyone;

class Store extends \BlueSpice\PageAssignments\Data\Assignable\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

}

<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

class Store extends \BlueSpice\Data\User\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

}

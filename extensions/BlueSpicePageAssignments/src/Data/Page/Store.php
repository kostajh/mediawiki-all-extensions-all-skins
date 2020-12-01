<?php

namespace BlueSpice\PageAssignments\Data\Page;

use BlueSpice\Data\Page\Store as PageStore;

class Store extends PageStore {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

}

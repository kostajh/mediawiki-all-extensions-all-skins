<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

use BlueSpice\Data\ReaderParams;

class Reader extends \BlueSpice\PageAssignments\Data\Assignable\Reader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	/**
	 *
	 * @return null
	 */
	public function makeSecondaryDataProvider() {
		return null;
	}
}

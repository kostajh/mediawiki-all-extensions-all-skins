<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

use BlueSpice\Data\ReaderParams;
use BlueSpice\PageAssignments\Data\Schema;

class Reader extends \BlueSpice\Data\DatabaseReader {

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
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return null
	 */
	public function makeSecondaryDataProvider() {
		return null;
	}

}

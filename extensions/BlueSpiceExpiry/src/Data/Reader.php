<?php

namespace BlueSpice\Expiry\Data;

use BlueSpice\Data\DatabaseReader;
use BlueSpice\Data\ReaderParams;

class Reader extends DatabaseReader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->getSchema() );
	}

	/**
	 *
	 * @return null
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}
}

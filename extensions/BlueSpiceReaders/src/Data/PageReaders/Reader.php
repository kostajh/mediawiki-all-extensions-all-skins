<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Data\ReaderParams;

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
	 * @return \BlueSpice\Readers\Data\Schema
	 */
	public function getSchema() {
		return new \BlueSpice\Readers\Data\Schema();
	}

	/**
	 *
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider();
	}

}

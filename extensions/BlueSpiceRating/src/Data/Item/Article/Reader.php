<?php

namespace BlueSpice\Rating\Data\Item\Article;

use BlueSpice\Data\ReaderParams;

class Reader extends \BlueSpice\Rating\Data\Item\Reader {

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
	 * @return SecondaryDataProvider
	 */
	protected function makeSecondaryDataProvider() {
		return new SecondaryDataProvider;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

}

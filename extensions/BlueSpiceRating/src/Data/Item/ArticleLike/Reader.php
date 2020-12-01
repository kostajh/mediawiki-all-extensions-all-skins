<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

class Reader extends \BlueSpice\Rating\Data\Item\Reader {

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	/**
	 *
	 * @return null
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

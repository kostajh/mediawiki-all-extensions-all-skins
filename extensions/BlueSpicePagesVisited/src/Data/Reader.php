<?php

namespace BlueSpice\PagesVisited\Data;

use BlueSpice\Data\ReaderParams;

class Reader extends \BlueSpice\WhoIsOnline\Data\Reader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->getSchema() );
	}

}

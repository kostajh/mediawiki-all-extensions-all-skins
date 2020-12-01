<?php

namespace BlueSpice\EchoConnector\Data\Watchlist;

use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\Watchlist\Reader as BaseReader;

class Reader extends BaseReader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, [] );
	}
}

<?php

namespace BlueSpice\WhoIsOnline\Data\Tracer;

use BlueSpice\WhoIsOnline\Data\Record;

class PrimaryDataProvider extends \BlueSpice\WhoIsOnline\Data\PrimaryDataProvider {

	/**
	 *
	 * @return array
	 */
	protected function getDefaultOptions() {
		return [
			'GROUP BY' => Record::USER_NAME,
			'ORDER BY' => 'MAX(' . Record::TIMESTAMP . ') DESC',
		];
	}

}

<?php

namespace BlueSpice\PagesVisited\Data;

use BlueSpice\WhoIsOnline\Data\Record;

class PrimaryDataProvider extends \BlueSpice\WhoIsOnline\Data\PrimaryDataProvider {

	/**
	 *
	 * @return array
	 */
	protected function getDefaultOptions() {
		return [
			'GROUP BY' => implode( ',', [
				Record::PAGE_ID,
				Record::PAGE_NAMESPACE,
				Record::PAGE_TITLE
			] ),
			'ORDER BY' => 'MAX(' . Record::TIMESTAMP . ') DESC',
		];
	}

}

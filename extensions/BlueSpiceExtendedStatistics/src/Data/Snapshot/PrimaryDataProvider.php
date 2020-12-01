<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use BlueSpice\Data\PrimaryDatabaseDataProvider;
use FormatJson;

class PrimaryDataProvider extends PrimaryDatabaseDataProvider {

	/**
	 *
	 * @return string[]
	 */
	protected function getTableNames() {
		return [ Schema::TABLE_NAME ];
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( \stdClass $row ) {
		$this->data[] = new Record( (object)[
			Record::ID => $row->{Record::ID},
			Record::DATA => FormatJson::decode( $row->{Record::DATA} ),
			Record::TIMESTAMP => $row->{Record::TIMESTAMP},
		] );
	}
}

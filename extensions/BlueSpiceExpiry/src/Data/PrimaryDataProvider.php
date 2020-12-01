<?php

namespace BlueSpice\Expiry\Data;

use BlueSpice\Data\PrimaryDatabaseDataProvider;

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
			Record::PAGE_ID => $row->{Record::PAGE_ID},
			Record::COMMENT => $row->{Record::COMMENT},
			Record::DATE => $row->{Record::DATE},
		] );
	}
}

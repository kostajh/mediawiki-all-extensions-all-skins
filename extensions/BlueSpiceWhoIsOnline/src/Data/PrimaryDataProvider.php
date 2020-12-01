<?php

namespace BlueSpice\WhoIsOnline\Data;

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
			Record::USER_ID => $row->{Record::USER_ID},
			Record::USER_NAME => $row->{Record::USER_NAME},
			Record::USER_REAL_NAME => $row->{Record::USER_REAL_NAME},
			Record::PAGE_ID => $row->{Record::PAGE_ID},
			Record::PAGE_NAMESPACE => $row->{Record::PAGE_NAMESPACE},
			Record::PAGE_TITLE => $row->{Record::PAGE_TITLE},
			Record::TIMESTAMP => $row->{Record::TIMESTAMP},
			Record::ACTION => $row->{Record::ACTION},
		] );
	}
}

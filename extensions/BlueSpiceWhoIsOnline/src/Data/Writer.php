<?php

namespace BlueSpice\WhoIsOnline\Data;

class Writer extends \BlueSpice\Data\DatabaseWriter {

	/**
	 *
	 * @return string[]
	 */
	protected function getIdentifierFields() {
		return [ Record::ID ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return Schema::TABLE_NAME;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema;
	}

}

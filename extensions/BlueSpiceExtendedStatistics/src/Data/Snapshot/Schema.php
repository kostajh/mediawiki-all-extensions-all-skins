<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	const TABLE_NAME = 'bs_extendedstatistics_snapshot';

	public function __construct() {
		parent::__construct( [
			Record::ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::DATA => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			Record::TIMESTAMP => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
		] );
	}
}

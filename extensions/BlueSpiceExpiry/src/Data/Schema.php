<?php

namespace BlueSpice\Expiry\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	public const TABLE_NAME = 'bs_expiry';

	public function __construct() {
		parent::__construct( [
			Record::ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::PAGE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::COMMENT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::DATE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
		] );
	}
}

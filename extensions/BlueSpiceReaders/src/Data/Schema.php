<?php

namespace BlueSpice\Readers\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	public function __construct() {
		parent::__construct( [
			Record::ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::USER_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::USER_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PAGE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::REV_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::TIMESTAMP => [
				self::FILTERABLE => false,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
			Record::USER_IMAGE_HTML => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
		] );
	}
}

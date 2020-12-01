<?php

namespace BlueSpice\WhoIsOnline\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	const TABLE_NAME = 'bs_whoisonline';

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
			Record::USER_REAL_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PAGE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::PAGE_NAMESPACE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::PAGE_TITLE => [
				self::FILTERABLE => false,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::TIMESTAMP => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
			Record::ACTION => [
				self::FILTERABLE => false,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
		] );
	}
}

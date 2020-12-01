<?php

namespace BlueSpice\Rating\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {

	/**
	 * @return Schema
	 */
	public function __construct() {
		parent::__construct( [
			Record::ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::REFTYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::REF => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::USERID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::USERIP => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::VALUE => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::MIXED
			],
			Record::CREATED => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
			Record::TOUCHED => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::DATE
			],
			Record::SUBTYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::CONTEXT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
		] );
	}
}

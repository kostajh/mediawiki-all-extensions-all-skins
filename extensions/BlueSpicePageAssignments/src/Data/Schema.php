<?php

namespace BlueSpice\PageAssignments\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	public function __construct() {
		parent::__construct( [
			Record::PAGE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::ASSIGNEE_TYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::ASSIGNEE_KEY => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::POSITION => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::ANCHOR => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			Record::ID => [
				self::FILTERABLE => false,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::TEXT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
		] );
	}
}

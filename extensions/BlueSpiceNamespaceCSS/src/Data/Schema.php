<?php

namespace BlueSpice\NamespaceCSS\Data;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	public function __construct() {
		parent::__construct( [
			Record::NAMESPACE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::NAMESPACE_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::SOURCE_PAGE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::SOURCE_PAGE_LINK => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
		] );
	}
}

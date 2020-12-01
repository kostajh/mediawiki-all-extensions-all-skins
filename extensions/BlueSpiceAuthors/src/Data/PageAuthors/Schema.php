<?php

namespace BlueSpice\Authors\Data\PageAuthors;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Schema {
	public function __construct() {
		parent::__construct( [
			Record::USER_IMAGE_HTML => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::TEXT
			],
			Record::USER_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::AUTHOR_TYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			]
		] );
	}
}

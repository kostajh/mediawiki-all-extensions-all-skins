<?php

namespace BlueSpice\CategoryManager\Data\Page;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Page\Schema as PageSchema;

class Schema extends \BlueSpice\Data\Schema {
	const TABLE_NAME = PageSchema::TABLE_NAME;

	public function __construct() {
		parent::__construct( array_merge( (array)( new PageSchema ), [
			Record::CATEGORIES => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PREFIXED_TEXT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
		] ) );
	}
}

<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Rating\Data\Item\Schema {
	public function __construct() {
		parent::__construct( [
			Record::PAGETITLE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PAGENAMESPACE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::TOTALCOUNT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
		] );
	}
}

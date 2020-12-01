<?php

namespace BlueSpice\Rating\Data\Item;

use BlueSpice\Data\FieldType;

abstract class Schema extends \BlueSpice\Data\Schema {
	/**
	 *
	 * @param Record[] $data
	 */
	public function __construct( $data ) {
		parent::__construct( array_merge( $data, [
			Record::REFTYPE => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			Record::REF => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			Record::SUBTYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::CONTENT => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
		] ) );
	}
}

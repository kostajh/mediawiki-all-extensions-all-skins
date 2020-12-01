<?php

namespace BlueSpice\Social\Topics\EntityListContext;

use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Social\Topics\Entity\Topic;

class SpecialTopics extends \BlueSpice\Social\EntityListContext {

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 15;
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Topic::ATTR_TYPE ]
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getSortProperty() {
		return Topic::ATTR_TIMESTAMP_CREATED;
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getTypeFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Topic::ATTR_TYPE,
			ListValue::KEY_VALUE => [ Topic::TYPE ],
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => \BlueSpice\Data\FieldType::LISTVALUE
		];
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntitySpawner() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	public function useEndlessScroll() {
		return true;
	}
}

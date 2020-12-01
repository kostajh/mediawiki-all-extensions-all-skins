<?php

namespace BlueSpice\Social\Profile\EntityListContext;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Social\Profile\Entity\Profile;

class SpecialProfiles extends EntityListContext {

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 10;
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getTypeFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Profile::ATTR_TYPE,
			ListValue::KEY_VALUE => [ Profile::TYPE ],
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => FieldType::LISTVALUE
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Profile::ATTR_TYPE ]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Profile::ATTR_TIMESTAMP_CREATED;
	}

}

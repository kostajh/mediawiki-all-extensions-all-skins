<?php

namespace BlueSpice\Social\EntityListContext;

use BlueSpice\Social\Entity;

class SpecialTimeline extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialTimelineTypeSelected';

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 20;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Entity::ATTR_TIMESTAMP_CREATED;
	}

	/**
	 *
	 * @return bool
	 */
	public function getPersistSettings() {
		return true;
	}
}

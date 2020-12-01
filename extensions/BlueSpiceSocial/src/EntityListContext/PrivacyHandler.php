<?php

namespace BlueSpice\Social\EntityListContext;

class PrivacyHandler extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListPrivacyHandlerTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListPrivacyHandlerTypeSelected';

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return -1;
	}
}

<?php

namespace BlueSpice\Social\EntityListContext;

class SpecialActivities extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_OUTPUT_TYPE = 'EntityListSpecialActivitiesOutputType';
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListSpecialActivitiesTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialActivitiesTypeSelected';

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 20;
	}
}

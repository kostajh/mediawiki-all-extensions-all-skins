<?php

namespace BlueSpice\PageAssignments\ConfigDefinition;

class LockdownGroups extends \BlueSpice\ConfigDefinition\GroupList {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_ADMINISTRATION . '/BlueSpicePageAssignments',
			static::MAIN_PATH_EXTENSION . '/BlueSpicePageAssignments/' . static::FEATURE_ADMINISTRATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpicePageAssignments',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-pageassignments-pref-lockdowngroups';
	}

	/**
	 *
	 * @return bool
	 */
	public function isHidden() {
		return !$this->config->get( 'PageAssignmentsUseAdditionalPermissions' );
	}
}

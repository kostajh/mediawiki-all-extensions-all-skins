<?php

namespace BlueSpice\Social\Topics\Hook\BSMigrateSettingsFromDeviatingNames;

use BlueSpice\Hook\BSMigrateSettingsFromDeviatingNames;

class MigrateSettings extends BSMigrateSettingsFromDeviatingNames {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( in_array( $this->oldName, $this->getSkipSettings() ) ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->newName = 'SocialTopicsTalkPageAutoCreate';
		return true;
	}

	/**
	 *
	 * @return array
	 */
	protected function getSkipSettings() {
		return [
			'MW::BSSocial::enTalkpageAutoCreate',
		];
	}
}

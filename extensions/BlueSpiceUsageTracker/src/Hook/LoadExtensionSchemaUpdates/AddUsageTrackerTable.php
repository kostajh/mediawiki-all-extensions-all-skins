<?php

namespace BS\UsageTracker\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddUsageTrackerTable extends LoadExtensionSchemaUpdates {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_usagetracker',
			"$dir/maintenance/db/bs_usagetracker.sql"
		);

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}

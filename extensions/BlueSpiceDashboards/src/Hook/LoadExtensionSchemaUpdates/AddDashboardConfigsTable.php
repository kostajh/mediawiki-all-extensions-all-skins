<?php

namespace BlueSpice\Dashboards\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddDashboardConfigsTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_dashboards_configs',
			"$dir/maintenance/db/mysql/bs_dashboards_configs.sql"
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}
}

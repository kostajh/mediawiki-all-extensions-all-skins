<?php

namespace BlueSpice\Dashboards\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddPostDatabaseUpdateMaintenance extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			\BSDashBoardsClearConfigMaintenance::class
		);

		$this->updater->addPostDatabaseUpdateMaintenance(
			\BsDashboardsUpdateRSSUrl::class
		);

		$this->updater->addPostDatabaseUpdateMaintenance(
			\BSDashboardsConvertToTwoColumns::class
		);
	}

}

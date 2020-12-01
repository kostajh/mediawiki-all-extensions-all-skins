<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddMigrateSettingsFromDBMaintenanceScript extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSRatingMigrateSettingsFromDB'
		);
		return true;
	}

}

<?php

namespace BlueSpice\Social\Topics\Hook\LoadExtensionSchemaUpdates;

class AddShoutboxMigrationMaintenanceScript extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSMigrateShoutbox'
		);
		return true;
	}

}

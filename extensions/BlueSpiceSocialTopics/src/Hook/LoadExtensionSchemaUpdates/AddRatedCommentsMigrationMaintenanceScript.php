<?php

namespace BlueSpice\Social\Topics\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatedCommentsMigrationMaintenanceScript extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSMigrateRatedComments'
		);
		return true;
	}

}

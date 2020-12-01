<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingRemoveArchivedMaintenanceScript extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSRatingRemoveArchived'
		);
		return true;
	}

}

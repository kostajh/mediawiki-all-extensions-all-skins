<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingSetDefaultSubTypeMaintenanceScript extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSRatingSetDefaultSubType'
		);
		return true;
	}

}

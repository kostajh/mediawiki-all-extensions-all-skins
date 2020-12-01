<?php

namespace BlueSpice\Rating\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddRatingMigrateRatedCommentsScript extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSRatingMigrateRatedComments'
		);
		return true;
	}

}

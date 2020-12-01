<?php

namespace BlueSpice\Social\Blog\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddBlogMigrationMaintenanceScript extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSMigrateBlog'
		);
		return true;
	}
}

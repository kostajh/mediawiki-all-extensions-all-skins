<?php

namespace BlueSpice\CustomMenu\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddMigrateTopBarMenu extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSCustomMenuMigrateTopBarMenu'
		);
		return true;
	}

}

<?php

namespace BlueSpice\Bookshelf\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddConvertContentModel extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'ConvertContentModel'
		);
		return true;
	}

}

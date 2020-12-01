<?php

namespace BlueSpice\NamespaceCSS\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;
use BlueSpice\NamespaceCSS\Maintenance\MoveToDotCSS;

class AddMoveToDotPostDatabaseMaintenance extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( MoveToDotCSS::class );
		return true;
	}
}

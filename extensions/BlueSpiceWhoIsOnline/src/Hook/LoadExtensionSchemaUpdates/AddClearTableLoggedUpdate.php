<?php

namespace BlueSpice\WhoIsOnline\Hook\LoadExtensionSchemaUpdates;

class AddClearTableLoggedUpdate extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			'ClearWhoIsOnlineTableAfterUpgradeTimestamp'
		);
		return true;
	}

}

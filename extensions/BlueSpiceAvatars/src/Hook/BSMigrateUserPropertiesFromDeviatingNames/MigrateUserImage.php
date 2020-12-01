<?php

namespace BlueSpice\Avatars\Hook\BSMigrateUserPropertiesFromDeviatingNames;

use BlueSpice\Hook\BSMigrateUserPropertiesFromDeviatingNames;

class MigrateUserImage extends BSMigrateUserPropertiesFromDeviatingNames {
	protected function skipProcessing() {
		if ( $this->oldName !== "MW::UserImage" ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->newName = "bs-avatars-profileimage";
		return true;
	}
}

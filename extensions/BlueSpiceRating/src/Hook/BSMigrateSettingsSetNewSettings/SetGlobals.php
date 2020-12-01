<?php

namespace BlueSpice\Rating\Hook\BSMigrateSettingsSetNewSettings;

use BlueSpice\Hook\BSMigrateSettingsSetNewSettings;

class SetGlobals extends BSMigrateSettingsSetNewSettings {

	protected function skipProcessing() {
		if ( !in_array( $this->newName, [
			"RatingArticleEnabledNamespaces",
			"RatingArticleLikeEnabledNamespaces"
		] ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$GLOBALS["bsg{$this->newName}"] = \FormatJson::decode( $this->newValue, true );

		$this->set = true;

		return false;
	}
}

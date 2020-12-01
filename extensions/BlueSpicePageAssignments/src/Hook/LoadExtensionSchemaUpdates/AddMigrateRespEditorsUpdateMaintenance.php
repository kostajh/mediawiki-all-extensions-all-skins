<?php

namespace BlueSpice\PageAssignments\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddMigrateRespEditorsUpdateMaintenance extends LoadExtensionSchemaUpdates {

	protected function skipProcessing() {
		if ( !$this->updater->tableExists( 'bs_responsible_editors' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance(
			\BSPageAssignmentsMigrateRespEditors::class
		);
	}

}

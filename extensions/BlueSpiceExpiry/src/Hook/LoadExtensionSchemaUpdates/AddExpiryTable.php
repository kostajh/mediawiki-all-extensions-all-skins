<?php

namespace BlueSpice\Expiry\Hook\LoadExtensionSchemaUpdates;

class AddExpiryTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_expiry',
			"$dir/maintenance/db/bs_expiry.sql"
		);
		// BS 2.23.3: Independence of Expiry from Reminder
		// add additional columns
		if ( $this->updater->getDB()->fieldExists( 'bs_expiry', 'expires' ) ) {
			$this->updater->addExtensionField(
				'bs_expiry',
				'exp_date',
				"$dir/maintenance/db/bs_expiry.patch.add_independence_cols.sql"
			);
			// copy date and page_id from reminder, delete all unused entries
			$this->updater->modifyExtensionField(
				'bs_expiry',
				'exp_date',
				"$dir/maintenance/db/bs_expiry.patch.copy_from_reminder_new_version.sql"
			);
			// delete expires column
			$this->updater->dropExtensionField(
				'bs_expiry',
				'expires',
				"$dir/maintenance/db/bs_expiry.patch.drop_exists_and_rem_id.sql"
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}

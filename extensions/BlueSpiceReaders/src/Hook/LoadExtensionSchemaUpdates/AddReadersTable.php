<?php

namespace BlueSpice\Readers\Hook\LoadExtensionSchemaUpdates;

class AddReadersTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_readers',
			"$dir/maintenance/db/bs_readers.sql"
		);

		$this->updater->addExtensionField(
			'bs_readers',
			'readers_ts',
			"$dir/maintenance/db/bs_readers.patch.readers_ts.sql"
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}

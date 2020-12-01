<?php

namespace BlueSpice\ExtendedStatistics\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddSnapshotTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_extendedstatistics_snapshot',
			"$dir/maintenance/db/bs_extendedstatistics_snapshot.sql"
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

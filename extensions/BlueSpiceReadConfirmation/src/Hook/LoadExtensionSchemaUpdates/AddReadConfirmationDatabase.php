<?php

namespace BlueSpice\ReadConfirmation\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddReadConfirmationDatabase extends LoadExtensionSchemaUpdates {
	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$dir = $this->getExtensionPath() . '/maintenance/db';

		$this->updater->addExtensionTable(
			'bs_readconfirmation',
			"$dir/bs_readconfirmation.sql"
		);
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}

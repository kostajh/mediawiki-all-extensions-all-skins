<?php

$extDir = dirname( dirname( __DIR__ ) );

require_once "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php";

class ClearWhoIsOnlineTableAfterUpgradeTimestamp extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		$this->output( "...bs_whoisonline -> clear table..." );

		$this->getDB( DB_MASTER )->delete(
			'bs_whoisonline',
			[ 'wo_id != 0' ],
			__METHOD__
		);
		$this->output( "OK\n" );
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'WhoIsOnline_clear_table';
	}

}

<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class BSDashboardsConvertToTwoColumns extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceFoundation' );
		$this->requireExtension( 'BlueSpiceDashboards' );
	}

	/**
	 *
	 * @return bool
	 */
	public function doDBUpdates() {
		$oDbr = $this->getDB( DB_REPLICA );
		$res = $oDbr->select( 'bs_dashboards_configs', [
			'dc_type', 'dc_identifier', 'dc_config'
		] );

		foreach ( $res as $row ) {
			$user = $row->dc_identifier;
			$type = $row->dc_type;

			if ( $type !== 'admin' && ( !$user || !$type ) ) {
				continue;
			}

			$config = FormatJson::decode( $row->dc_config );
			if ( count( $config ) !== 3 ) {
				continue;
			}
			$config[1] = array_merge( $config[1], $config[2] );
			unset( $config[2] );

			$oDbw = $this->getDB( DB_MASTER );
			$oDbw->update(
				'bs_dashboards_configs',
				[
					// save json string into db
					'dc_config' => FormatJson::encode( $config )
				],
				[
					'dc_type' => $type,
					'dc_identifier' => $user
				]
			);
		}

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_dashboards-convert-to-two-columns';
	}
}

$maintClass = 'BSDashboardsConvertToTwoColumns';
require_once RUN_MAINTENANCE_IF_MAIN;

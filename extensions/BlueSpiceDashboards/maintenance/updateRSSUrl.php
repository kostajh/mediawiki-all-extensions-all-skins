<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class BsDashboardsUpdateRSSUrl extends LoggedUpdateMaintenance {
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
		$this->output( 'Updating RSS Url... ' );
		$oDbr = $this->getDB( DB_REPLICA );
		$res = $oDbr->select( 'bs_dashboards_configs', '*', [], __METHOD__ );

		foreach ( $res as $row ) {
			$portletConfig = false;
			$hasChange = false;

			try{
				Wikimedia\suppressWarnings();
				// backward compatible handling
				$portletConfig = unserialize( $row->dc_config );
				Wikimedia\restoreWarnings();
			}catch ( Exception $e ) {
				$this->output( "Object in json only string\n" );
			}
			if ( $portletConfig === false ) {
				// this should be the normal case
				$portletConfig = FormatJson::decode( $row->dc_config );
			} else {
				$portletConfig = FormatJson::decode( $portletConfig );
				$this->output( "Object in serialized json\n" );
			}

			foreach ( $portletConfig[0] as &$portlet ) {
				if ( !is_object( $portlet ) || !property_exists( $portlet, 'type' ) ) {
					continue;
				}
				if ( $portlet->type !== 'BS.RSSFeeder.RSSPortlet' ) {
					continue;
				}
				if (
					!is_object( $portlet->config ) ||
					!property_exists( $portlet->config, 'rssurl' )
				) {
					continue;
				}
				if ( $portlet->config->rssurl !== 'http://blog.bluespice.com/feed/' ) {
					continue;
				}

				$portlet->config->rssurl = 'https://blog.bluespice.com/feed/';
				$hasChange = true;
			}

			if ( $hasChange ) {
				$portletConfig = FormatJson::encode( $portletConfig );
				$oDbw = $this->getDB( DB_MASTER );
				$oDbw->update(
					'bs_dashboards_configs',
					[
						'dc_config' => $portletConfig
					],
					[
						'dc_type' => $row->dc_type,
						'dc_identifier' => $row->dc_identifier
					],
					__METHOD__
				);
			}
		}

		$this->output( 'ok' . PHP_EOL );

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_dashboards-update-rss-url';
	}
}

$maintClass = 'BsDashboardsUpdateRSSUrl';
require_once RUN_MAINTENANCE_IF_MAIN;

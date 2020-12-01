<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

use MediaWiki\MediaWikiServices;

class BSDashBoardsClearConfigMaintenance extends LoggedUpdateMaintenance {
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
		$aFinalPortletList = [];
		$aPortlets = [];

		$this->getServices()->getHookContainer()->run(
			'BSDashboardsUserDashboardPortalPortlets',
			[
				&$aPortlets
			]
		);
		$this->getServices()->getHookContainer()->run(
			'BSDashboardsAdminDashboardPortalPortlets',
			[
				&$aPortlets
			]
		);
		$this->getServices()->getHookContainer()->run( 'BSDashboardsGetPortlets', [
			&$aPortlets
		] );
		$this->output( 'Clearing dashboards... ' );
		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			$aFinalPortletList[] = $aPortlets[$i]["type"];
		}

		$oDbr = $this->getDB( DB_REPLICA );
		$res = $oDbr->select( 'bs_dashboards_configs', '*' );

		foreach ( $res as $row ) {
			$iUser = $row->dc_identifier;
			$sType = $row->dc_type;
			$bHasChange = false;

			try{
				Wikimedia\suppressWarnings();
				// backward compatible handling
				$aPortalConfig = unserialize( $row->dc_config );
				Wikimedia\restoreWarnings();
			}catch ( Exception $e ) {
				$this->output( "Object in json only string\n" );
			}
			if ( $aPortalConfig === false ) {
				// this should be the normal case
				$aPortalConfig = FormatJson::decode( $row->dc_config );
			} else {
				$aPortalConfig = FormatJson::decode( $aPortalConfig );
				$this->output( "Object in serialized json\n" );
				$bHasChange = true;
			}

			for ( $x = 0; $x < count( $aPortalConfig ); $x++ ) {
				for ( $y = 0; $y < count( $aPortalConfig[$x] ); $y++ ) {
					if ( !in_array( $aPortalConfig[$x][$y]->type, $aFinalPortletList ) ) {
						$this->output( "Will remove " . $aPortalConfig[$x][$y]->type );
						unset( $aPortalConfig[$x][$y] );
						$bHasChange = true;
					}
				}
			}
			$aPortalConfig = FormatJson::encode( $aPortalConfig );
			if ( $bHasChange ) {
				$this->output( "Save changes to database\n" );
				$oDbw = $this->getDB( DB_MASTER );
				$oDbw->update(
					'bs_dashboards_configs',
					[
						// save json string into db
						'dc_config' => $aPortalConfig
					],
					[
						'dc_type' => $sType,
						'dc_identifier' => $iUser
					]
				);
			}
		}

		return true;
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	protected function getServices() {
		return MediaWiki\MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_dashboards-clear-configs';
	}
}

$maintClass = 'BSDashBoardsClearConfigMaintenance';
require_once RUN_MAINTENANCE_IF_MAIN;

<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\MediaWikiServices;

class BSCustomMenuMigrateTopBarMenu extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return bool
	 */
	protected function noDataToMigrate() {
		$oldTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"TopBarMenu"
		);
		if ( !$oldTitle || !$oldTitle->exists() ) {
			return true;
		}
		$newTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		if ( $newTitle && $newTitle->exists() ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		if ( $this->noDataToMigrate() ) {
			$this->output( "TopBarMenu -> No data to migrate\n" );
			return true;
		}
		$this->output( "...TopBarMenu -> migration...\n" );

		$oldTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"TopBarMenu"
		);
		$newTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		try{
			$move = new \MovePage( $oldTitle, $newTitle );
			$move->move(
				$this->getMaintenanceUser(),
				"TopMenuBarCustomizer => CustomMenu",
				false
			);
		} catch ( \Exception $e ) {
			$this->output( $e->getMessage() );
		}
		$this->output( "\n" );

		return true;
	}

	/**
	 *
	 * @return User
	 */
	protected function getMaintenanceUser() {
		return MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'TopBarMenu';
	}

}

<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\MediaWikiServices;

class BSRatingMigrateSettingsFromDB extends LoggedUpdateMaintenance {

	/**
	 * @inheritDoc
	 */
	protected function doDBUpdates() {
		$this->output( 'Migrating settings for BlueSpiceRating to nm-settings.php...' );

		$namespaceManager = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceNamespaceManager' );
		if ( !$namespaceManager ) {
			$this->output( 'BlueSpiceNamespaceManager is not enabled' . PHP_EOL );
			return false;
		}

		$ratingArticleEnabledNamespaces = $this->getValuesFor( 'RatingArticleEnabledNamespaces' );
		$ratingArticleLikeEnabledNamespaces = $this->getValuesFor( 'RatingArticleLikeEnabledNamespaces' );

		$userNamespaces = NamespaceManager::getUserNamespaces( true );
		$this->buildDefinitions(
			'rating',
			$ratingArticleEnabledNamespaces,
			$userNamespaces
		);
		$this->buildDefinitions(
			'recommendations',
			$ratingArticleLikeEnabledNamespaces,
			$userNamespaces
		);
		$status = NamespaceManager::setUserNamespaces( $userNamespaces );
		if ( !$status['success'] ) {
			$this->output( 'failed:' . $status['message'] . PHP_EOL );
			return false;
		}

		if (
			$this->deleteDBSettings( 'RatingArticleEnabledNamespaces' ) &&
			$this->deleteDBSettings( 'RatingArticleLikeEnabledNamespaces' )
		) {
			$this->output( 'failed removing settings from database' . PHP_EOL );
			return false;
		}

		$this->output( 'done' . PHP_EOL );
		return true;
	}

	/**
	 * @param string $prop
	 * @return bool
	 */
	protected function deleteDBSettings( $prop ) {
		$db = $this->getDB( DB_MASTER );
		$db->delete(
			'bs_settings3',
			[ 's_name' => $prop ],
			__METHOD__
		);

		if ( $db->affectedRows() === 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $varName
	 * @param array $namespaces
	 * @param array &$userNamespaces
	 * @return array
	 */
	protected function buildDefinitions( $varName, $namespaces, &$userNamespaces ) {
		$aliases = $GLOBALS['wgNamespaceAliases'];
		$defs = [];
		foreach ( $namespaces as $id ) {
			if ( !isset( $userNamespaces[$id] ) ) {
				$userNamespaces[$id] = [
					'name' => BsNamespaceHelper::getNamespaceName( $id ),
					'alias' => array_search( $id, $aliases ) ?: ''
				];
			}
			$userNamespaces[$id][$varName] = true;
		}

		return $defs;
	}

	/**
	 * @param string $prop
	 * @return array
	 */
	protected function getValuesFor( $prop ) {
		$res = $this->getDB( DB_REPLICA )->selectRow(
			'bs_settings3',
			[ 's_value' ],
			[ 's_name' => $prop ],
			__METHOD__
		);

		if ( !$res ) {
			return [];
		}

		$val = $res->s_value;
		$parsed = eval( "return $val;" );
		if ( is_array( $parsed ) ) {
			return $parsed;
		}

		return [];
	}

	/**
	 * Get the update key name to go in the update log table
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating_settings_remove';
	}

}

$maintClass = BSRatingMigrateSettingsFromDB::class;
require_once RUN_MAINTENANCE_IF_MAIN;

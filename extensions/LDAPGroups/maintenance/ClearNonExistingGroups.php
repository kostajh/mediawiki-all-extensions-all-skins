<?php

namespace MediaWiki\Extension\LDAPGroups\Maintenance;

use Maintenance;
use User;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
			  ? getenv( 'MW_INSTALL_PATH' )
			  : __DIR__ . '/../../..' ) . '/maintenance/Maintenance.php';
if ( !file_exists( $maintPath ) ) {
	echo "Please set the environment variable MW_INSTALL_PATH "
		. "to your MediaWiki installation.\n";
	exit( 1 );
}
require_once $maintPath;

class ClearNonExistingGroups extends Maintenance {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->addOption( 'dry', 'Do not really apply changes' );
	}

	/**
	 *
	 */
	public function execute() {
		$this->output( "This will remove all groups from the database,"
			. " that are not configured locally!\n" );
		$this->countDown( 5 );

		$dryRun = false;
		if ( $this->getOption( 'dry' ) !== null ) {
			$dryRun = true;
		}

		// e.g. [ 'A', 'B', 'C' ]
		$locallyAvailableGroups = User::getAllGroups();
		$dbr = $this->getDB( DB_REPLICA );
		$res = $dbr->select( 'user', '*' );
		foreach ( $res as $row ) {
			$user = User::newFromRow( $row );

			$this->output( "User '{$user->getName()}' ..." );
			// e.g. [ 'A', 'B', 'D' ]
			$userGroups = $user->getGroups();

			$groupsToRemove = [];
			foreach ( $userGroups as $group ) {
				if ( !in_array( $group, $locallyAvailableGroups ) ) {
					$groupsToRemove[] = $group;
				}
			}

			foreach ( $groupsToRemove as $groupToRemove ) {
				$this->output( "    removing '$groupToRemove'\n" );
				if ( !$dryRun ) {
					$user->removeGroup( $groupToRemove );
				}
			}
			$this->output( "done.\n" );
		}
	}

}

$maintClass = ClearNonExistingGroups::class;
require_once RUN_MAINTENANCE_IF_MAIN;

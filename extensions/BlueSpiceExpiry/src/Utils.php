<?php

namespace BlueSpice\Expiry;

use MediaWiki\MediaWikiServices;
use Title;
use Wikimedia\Rdbms\LoadBalancer;

class Utils {

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 *
	 * @param int $type
	 * @return \DatabaseBase
	 */
	protected function getDB( $type = DB_REPLICA ) {
		return $this->loadBalancer->getConnection( $type );
	}

	/**
	 * TODO: Replace with registry!
	 * @param Title $title
	 * @return \User[]
	 */
	public function getPageModerators( $title ) {
		$moderators = [];

		// This is not nice. A hook should be added and 'PageAssignments' (or
		// some other provider) should then fill the list
		$services = MediaWikiServices::getInstance();
		if ( !$services->hasService( 'BSPageAssignmentsAssignmentFactory' ) ) {
			return $moderators;
		}
		$factory = $services->getService( 'BSPageAssignmentsAssignmentFactory' );
		$target = $factory->newFromTargetTitle( $title );
		if ( !$target ) {
			return $moderators;
		}
		foreach ( $target->getAssignedUserIDs() as $userID ) {
			$assigneduser = \User::newFromId( $userID );
			if ( !$assigneduser ) {
				continue;
			}
			$assigneduser->load();
			if ( $assigneduser->isAnon() ) {
				// Clear out fragments to prevent `Echo` from breaking
				continue;
			}
			$moderators[] = $assigneduser;
		}

		return $moderators;
	}

	/**
	 * TODO: Replace with store soon!
	 * @return \User[]
	 */
	public function getSysops() {
		$res = $this->getDB()->select(
			'user_groups',
			'ug_user',
			[ 'ug_group' => 'sysop' ]
		);

		$sysops = [];

		foreach ( $res as $row ) {
			$sysop = \User::newFromId( $row->ug_user );
			if ( $sysop instanceof \User ) {
				$sysop->load();
				if ( $sysop->isAnon() ) {
					// Clear out fragments to prevent `Echo` from breaking
					continue;
				}
				$sysops[] = $sysop;
			}
		}

		return $sysops;
	}
}

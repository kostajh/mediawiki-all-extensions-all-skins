<?php

namespace BlueSpice\PageAssignments\Permission\Lockdown\Module\Groups;

use BlueSpice\Permission\Lockdown\Module\Groups\SubModule;
use Title;
use User;

class PageAssignment extends SubModule {

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function applies( Title $title, User $user ) {
		if ( !$this->getConfig()->get( 'PageAssignmentsUseAdditionalPermissions' ) ) {
			return false;
		}
		return $title instanceof Title && $title->getNamespace() >= 0;
	}

	/**
	 *
	 * @param User $user
	 * @return string[]
	 */
	public function getLockdownGroups( User $user ) {
		return $this->getConfig()->get( 'PageAssignmentsLockdownGroups' );
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return bool
	 */
	public function mustLockdown( Title $title, User $user, $action ) {
		if ( $title->exists() === false ) {
			return true;
		}
		$factory = $this->getServices()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$target = $factory->newFromTargetTitle( $title );
		if ( $target && $target->isUserAssigned( $user ) ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return Message
	 */
	public function getLockdownReason( Title $title, User $user, $action ) {
		return $this->msg(
			'bs-pageassignments-group-lockdown-reason',
			$title->getFullText()
		);
	}

}

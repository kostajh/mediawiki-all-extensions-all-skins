<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\BaseNotification;

class GroupsRemove extends BaseNotification {
	/**
	 * @var array
	 */
	protected $groupsRemoved;

	/**
	 *
	 * @param \User $agent
	 * @param \User $user
	 * @param array $groupsRemoved
	 */
	public function __construct( $agent, $user, $groupsRemoved ) {
		$pageAssignmentsSpecial = \SpecialPage::getTitleFor( 'PageAssignments' );
		parent::__construct( 'bs-pageassignments-user-group-remove', $agent, $pageAssignmentsSpecial );

		$this->addAffectedUsers( [ $user ] );
		$this->groupsRemoved = $groupsRemoved;
		if ( $user->getId() == $agent->getId() ) {
			$this->setNotifyAgent( true );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getParams() {
		return [
			'group' => implode( ', ', $this->groupsRemoved ),
			'groupcount' => count( $this->groupsRemoved )
		];
	}

	/**
	 *
	 * @param bool $notify
	 */
	protected function setNotifyAgent( $notify ) {
		$this->extra['notifyAgent'] = $notify;
	}
}

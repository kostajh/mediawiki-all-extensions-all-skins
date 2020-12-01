<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\BaseNotification;

class AssignmentChangeRemove extends BaseNotification {
	/**
	 *
	 * @param \User $agent
	 * @param \Title $title
	 * @param \User[] $affectedUsers
	 */
	public function __construct( $agent, $title, $affectedUsers ) {
		parent::__construct( 'bs-pageassignments-assignment-change-remove', $agent, $title );
		$this->addAffectedUsers( $affectedUsers );
		$this->setUseJobQueue( true );
	}

	/**
	 *
	 * @return array
	 */
	public function getParams() {
		return [
			'titlelink' => true
		];
	}
}

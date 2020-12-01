<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\BaseNotification;

class AssignmentChangeAdd extends BaseNotification {

	/**
	 *
	 * @param \User $agent
	 * @param \Title $title
	 * @param \User[] $affectedUsers
	 */
	public function __construct( $agent, $title, $affectedUsers ) {
		parent::__construct( 'bs-pageassignments-assignment-change-add', $agent, $title );
		$this->addAffectedUsers( $affectedUsers );
		$this->setUseJobQueue( true );
	}

	/**
	 *
	 * @return type
	 */
	public function getParams() {
		return [
			'titlelink' => true
		];
	}
}

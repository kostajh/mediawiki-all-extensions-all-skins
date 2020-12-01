<?php

namespace BlueSpice\ReadConfirmation\Notifications;

use BlueSpice\BaseNotification;

class Remind extends BaseNotification {

	/**
	 * @var array
	 */
	protected $affectedUsers = [];

	/**
	 * Remind constructor.
	 * @param \User $agent
	 * @param \Title|null $title
	 * @param array $extraParams
	 * @param array $affectedUsers
	 */
	public function __construct(
		\User $agent, \Title $title = null, $extraParams = [], $affectedUsers = []
	) {
		parent::__construct( 'bs-readconfirmation-remind', $agent, $title, $extraParams );
		$this->affectedUsers = $affectedUsers;
	}

	/**
	 * @return array|null
	 */
	public function getAudience() {
		return $this->affectedUsers;
	}
}

<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;
use RequestContext;

class DeleteNotification extends BaseNotification {
	/**
	 * @var \Title
	 */
	protected $deletedTitle;

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 *
	 * @param \User $agent
	 * @param \Title $title
	 * @param string $reason
	 */
	public function __construct( $agent, $title, $reason ) {
		parent::__construct( 'bs-delete', $agent );

		// This title does not longer exists, so it cannot
		// be set as regular title
		$this->deletedTitle = $title;
		$this->reason = $reason;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams() {
		return [
			'deletereason' => $this->reason,
			'realname' => $this->getUserRealName(),
			'title' => $this->deletedTitle,
			'time' => RequestContext::getMain()->getLanguage()->timeanddate(
				$this->deletedTitle->getTouched(), true
			)
		];
	}
}

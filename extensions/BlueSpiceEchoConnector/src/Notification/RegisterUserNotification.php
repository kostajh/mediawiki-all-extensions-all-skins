<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;

class RegisterUserNotification extends BaseNotification {
	/**
	 * @var \User
	 */
	protected $createdUser;

	/**
	 *
	 * @param \User $agent
	 * @param \User $createdUser
	 */
	public function __construct( $agent, $createdUser ) {
		parent::__construct( 'bs-registeruser', $agent, $createdUser->getUserPage() );
		$this->createdUser = $createdUser;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams() {
		return [
			'realname' => $this->getRealNameText(),
			'user' => $this->createdUser
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getSecondaryLinks() {
		return [
			'performer' => [
				'url' => $this->agent->getUserPage()->getFullURL(),
				'label-params' => [ $this->getUserRealName() ]
			]
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function getRealNameText() {
		$realname = $this->getUserRealName( $this->createdUser );

		if ( $realname !== $this->createdUser->getName() ) {
			$realname = wfMessage(
				'bs-notifications-param-realname-with-username',
				$realname,
				$this->createdUser->getName()
			)->plain();
		}
		return $realname;
	}
}

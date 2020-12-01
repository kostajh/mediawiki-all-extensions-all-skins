<?php

namespace BlueSpice\EchoConnector\Hook;

use Config;
use EchoEvent;
use IContextSource;
use User;

abstract class EchoGetNotificationTypes extends \BlueSpice\Hook {
	/**
	 *
	 * @var User
	 */
	protected $user;

	/**
	 *
	 * @var EchoEvent
	 */
	protected $event;

	/**
	 *
	 * @var array
	 */
	protected $userNotifyTypes;

	/**
	 *
	 * @param User $user
	 * @param EchoEvent $event
	 * @param array &$userNotifyTypes
	 * @return bool|null
	 */
	public static function callback( $user, $event, &$userNotifyTypes ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$user,
			$event,
			$userNotifyTypes
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param User $user
	 * @param EchoEvent $event
	 * @param array &$userNotifyTypes
	 */
	public function __construct( $context, $config, $user, $event, &$userNotifyTypes ) {
		parent::__construct( $context, $config );

		$this->user = $user;
		$this->event = $event;
		$this->userNotifyTypes = &$userNotifyTypes;
	}
}

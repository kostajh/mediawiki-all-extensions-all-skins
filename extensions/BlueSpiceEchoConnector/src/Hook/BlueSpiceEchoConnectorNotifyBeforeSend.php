<?php

namespace BlueSpice\EchoConnector\Hook;

use BlueSpice\Hook;
use Config;
use EchoEvent;
use IContextSource;
use User;

abstract class BlueSpiceEchoConnectorNotifyBeforeSend extends Hook {
	/** @var EchoEvent */
	protected $event;

	/** @var User */
	protected $user;

	/** @var string */
	protected $deliveryMethod;

	/**
	 *
	 * @param EchoEvent &$event
	 * @param User $user
	 * @param string $deliveryMethod
	 * @return bool|null
	 */
	public static function callback( &$event, $user, $deliveryMethod ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$event,
			$user,
			$deliveryMethod
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param EchoEvent &$event
	 * @param User $user
	 * @param string $deliveryMethod
	 */
	public function __construct( $context, $config, &$event, $user, $deliveryMethod ) {
		parent::__construct( $context, $config );

		$this->event =& $event;
		$this->user = $user;
		$this->deliveryMethod = $deliveryMethod;
	}
}

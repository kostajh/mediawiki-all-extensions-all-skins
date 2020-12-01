<?php

namespace BlueSpice\EchoConnector\Hook;

use BlueSpice\Hook;
use Config;
use IContextSource;

abstract class BlueSpiceEchoConnectorUserLocatorValidUsers extends Hook {
	/**
	 * Array of valid users that will be be sent notifications [ id => User ]
	 * @var User[]
	 */
	protected $users = null;

	/**
	 * Can legit be null!
	 * @var Title|null
	 */
	protected $title = null;

	/**
	 * Located in BlueSpice\EchoConnector\UserLocator::getValidUsersFromIds.
	 * @param User[] &$users
	 * @param Title|null $title
	 * @return bool
	 */
	public static function callback( &$users, $title ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$users,
			$title
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param User[] &$users
	 * @param Title|null $title
	 */
	public function __construct( $context, $config, &$users, $title ) {
		parent::__construct( $context, $config );

		$this->users = &$users;
		$this->title = $title;
	}
}

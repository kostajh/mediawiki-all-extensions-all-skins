<?php

namespace BlueSpice\TagCloud;

use BlueSpice\Config;
use IContextSource;
use User;

class Context extends \BlueSpice\Context {

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param User|null $user | null
	 */
	public function __construct( \IContextSource $context, Config $config, \User $user = null ) {
		parent::__construct( $context, $config );
		$this->user = $user;
	}

	/**
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user ) {
			return $this->user;
		}
		return parent::getUser();
	}
}

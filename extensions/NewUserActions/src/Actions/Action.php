<?php

namespace MediaWiki\Extensions\NewUserActions\Actions;

use User;

abstract class Action {
	/**
	 * The newly created user
	 *
	 * @var User
	 */
	protected $user;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	abstract public function run();
}

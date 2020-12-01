<?php

namespace MediaWiki\Extensions\NewUserActions;

use MediaWiki\Extensions\NewUserActions\Actions\CreateNewUserPage;

use User;

class Actions {

	/**
	 * The newly created user
	 *
	 * @var User
	 */
	private $user;

	/**
	 * The list of actions to run
	 *
	 * @var MediaWiki\Extensions\NewUserActions\Actions\Action[]
	 */
	private $actions = [];

	///
	/// Constructor
	///

	/**
	 * Initializes a new instance of the Actions class.
	 *
	 * @param User $user The newly created user
	 */
	public function __construct( User $user ) {
		$this->user = $user;
	}

	///
	/// Public methods
	///

	/**
	 * Configures the actions to run.
	 */
	public function configureActions() {
		global $wgNewUserActionsCreateUserPage;

		if ( $wgNewUserActionsCreateUserPage ) {
			$this->actions[] = new CreateNewUserPage( $this->user );
		}
	}

	/**
	 * Runs each action sequentially.
	 */
	public function runActions() {
		foreach ( $this->actions as $action ) {
			$action->run();
		}
	}

	///
	/// Static helper methods
	///

	/**
	 * Initializes a new instance of the Actions class, populates and runs it.
	 * @param User $user
	 */
	static function run( User $user ) {
		$instance = new static( $user );
		$instance->configureActions();
		$instance->runActions();
	}

}

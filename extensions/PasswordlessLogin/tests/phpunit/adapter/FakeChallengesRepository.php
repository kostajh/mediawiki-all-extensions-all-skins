<?php

namespace PasswordlessLogin\adapter;

use PasswordlessLogin\model\Challenge;
use PasswordlessLogin\model\ChallengesRepository;
use User;

class FakeChallengesRepository implements ChallengesRepository {
	/** @var Challenge */
	public $byUser;
	/** @var Challenge */
	public $savedChallenge;
	/**
	 * @var User
	 */
	public $removed;

	/**
	 * @inheritDoc
	 */
	public function save( Challenge $challenge ) {
		$this->savedChallenge = $challenge;
	}

	/**
	 * @inheritDoc
	 */
	public function findByChallenge( $challenge ) {
	}

	/**
	 * @inheritDoc
	 */
	public function findByUser( User $user ) {
		return $this->byUser;
	}

	/**
	 * @inheritDoc
	 */
	public function remove( User $user ) {
		$this->removed = $user;
	}
}

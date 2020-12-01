<?php

namespace PasswordlessLogin\model;

use User;

interface ChallengesRepository {
	const SERVICE_NAME = 'PasswordlessLogin.ChallengesRepository';

	/**
	 * Persist a challenge.
	 *
	 * @param Challenge $challenge
	 */
	public function save( Challenge $challenge );

	/**
	 * @param string $challenge
	 * @return Challenge
	 */
	public function findByChallenge( $challenge );

	/**
	 * @param User $user
	 * @return Challenge
	 */
	public function findByUser( User $user );

	/**
	 * Removes all challenges for the use.r
	 *
	 * @param User $user
	 */
	public function remove( User $user );
}

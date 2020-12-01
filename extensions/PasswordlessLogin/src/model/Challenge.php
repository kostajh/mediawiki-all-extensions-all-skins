<?php

namespace PasswordlessLogin\model;

use Exception;
use User;

class Challenge {
	private $challenge;
	private $userId;
	private $success = false;

	/**
	 * Whether the challenge is solved or not.
	 *
	 * @return bool
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * Set whether the challenge is solved or not.
	 *
	 * @param bool $success
	 */
	public function setSuccess( $success ) {
		$this->success = $success;
	}

	/**
	 * Challenge constructor.
	 * @param int $userId
	 * @param string $challenge
	 * @param bool $success
	 */
	public function __construct( $userId, $challenge, $success = false ) {
		$this->challenge = $challenge;
		$this->userId = $userId;
		$this->success = $success;
	}

	/**
	 * Generates a new Challenge for the provided user.
	 *
	 * @param User $user
	 * @return Challenge
	 * @throws Exception
	 */
	public static function forUser( User $user ) {
		return new Challenge( $user->getId(), bin2hex( random_bytes( 8 ) ) );
	}

	/**
	 * Get the randomly generated challenge the opposing party needs to solve.
	 *
	 * @return mixed
	 */
	public function getChallenge() {
		return $this->challenge;
	}

	/**
	 * The User ID this Challenge object was created for.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Verify if the provided response is a valid solution for the challenge.
	 *
	 * @param Device $device The device for which the challenge was generated.
	 * @param string $response The solution for the Challenge provided by the opposing party
	 * @return bool
	 */
	public function verify( Device $device, $response ) {
		$calculatedMac = hash_hmac( "sha512", $this->getChallenge(), $device->getSecret() );

		$this->success = $calculatedMac === $response;

		return $this->success;
	}
}

<?php

namespace PasswordlessLogin\model;

use Exception;
use User;

class Device {
	private $id;
	private $deviceId;
	private $deviceUserId;
	private $devicePairToken;
	private $secret;
	private $confirmed = false;

	/**
	 * The device ID, which was provided by the device during the registration.
	 *
	 * @return string
	 */
	public function getDeviceId() {
		return $this->deviceId;
	}

	/**
	 * @param string $deviceId
	 */
	public function setDeviceId( $deviceId ) {
		$this->deviceId = $deviceId;
	}

	/**
	 * The secret which was provided by the device during the registration.
	 *
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret( $secret ) {
		$this->secret = $secret;
	}

	/**
	 * The User ID of the user this device belongs to.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->deviceUserId;
	}

	/**
	 * Device constructor.
	 * @param int $deviceUserId
	 * @param null|string $deviceId
	 * @param null|int $id
	 * @param null|string $secret
	 * @param bool $confirmed
	 */
	public function __construct(
		$deviceUserId, $deviceId = null, $id = null, $secret = null, $confirmed = false
	) {
		$this->id = $id;
		$this->deviceId = $deviceId;
		$this->deviceUserId = $deviceUserId;
		$this->secret = $secret;
		$this->confirmed = $confirmed;
	}

	/**
	 * Constructs a new Device for the given user.
	 *
	 * @param User $user
	 * @return Device
	 * @throws Exception
	 */
	public static function forUser( User $user ) {
		$device = new Device( $user->getId() );
		$device->devicePairToken = bin2hex( random_bytes( 16 ) );

		return $device;
	}

	/**
	 * Returns the pair token that needs to be used when an actual device is trying to register.
	 *
	 * @return null|string Null if the device is already registered, the pair token otherwise
	 */
	public function getPairToken() {
		return $this->devicePairToken;
	}

	/**
	 * Confirms the device registration. The caller needs to ensure that the device registration
	 * was actually proven to be successful before the device is confirmed. It's the task of the
	 * caller to appropriately check that the registration succeeded.
	 */
	public function confirm() {
		$this->confirmed = true;
	}

	/**
	 * Whether the device registration was confirmed to be succeeded.
	 *
	 * @return bool
	 */
	public function isConfirmed() {
		return $this->confirmed;
	}
}

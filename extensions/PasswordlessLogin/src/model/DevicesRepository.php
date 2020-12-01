<?php

namespace PasswordlessLogin\model;

use User;

interface DevicesRepository {
	const SERVICE_NAME = 'PasswordlessLogin.DevicesRepository';

	/**
	 * @param Device $device
	 * @return void
	 */
	public function save( Device $device );

	/**
	 * @param int $userId
	 * @return Device|null
	 */
	public function findByUserId( $userId );

	/**
	 * @param string $pairToken
	 * @return Device|null
	 */
	public function findByPairToken( $pairToken );

	/**
	 * @param User $user
	 * @return void
	 */
	public function remove( User $user );
}

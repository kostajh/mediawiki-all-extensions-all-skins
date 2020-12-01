<?php

namespace PasswordlessLogin\adapter;

use PasswordlessLogin\model\Device;
use PasswordlessLogin\model\DevicesRepository;
use User;

class FakeDevicesRepository implements DevicesRepository {
	/** @var Device|null */
	public $byUserId = null;
	public $removedFor = null;
	/** @var Device|null */
	public $savedDevice = null;

	/**
	 * @inheritDoc
	 */
	public function findByUserId( $userId ) {
		return $this->byUserId;
	}

	/**
	 * @inheritDoc
	 */
	public function remove( User $user ) {
		$this->removedFor = $user;
	}

	/**
	 * @inheritDoc
	 */
	public function save( Device $device ) {
		$this->savedDevice = $device;
	}

	/**
	 * @inheritDoc
	 */
	public function findByPairToken( $pairToken ) {
	}
}

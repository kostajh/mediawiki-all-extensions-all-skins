<?php

namespace PasswordlessLogin\adapter;

use PasswordlessLogin\model\Device;
use PasswordlessLogin\model\DevicesRepository;
use User;
use Wikimedia\Rdbms\IResultWrapper;
use Wikimedia\Rdbms\LoadBalancer;

class DatabaseDeviceRepository implements DevicesRepository {
	const TABLE_NAME = 'passwordlesslogin_devices';
	private $FETCH_FIELDS = [ 'id', 'device_id', 'device_user_id', 'secret', 'confirmed' ];
	private $loadBalancer;

	/**
	 * DatabaseDeviceRepository constructor.
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( LoadBalancer $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @inheritDoc
	 */
	public function findByUserId( $userId ) {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$result =
			$dbr->select( self::TABLE_NAME, $this->FETCH_FIELDS, [ 'device_user_id' => $userId ] );

		return $this->extractDevice( $result );
	}

	/**
	 * @inheritDoc
	 */
	public function remove( User $user ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->delete( self::TABLE_NAME, [
			'device_user_id' => $user->getId(),
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function save( Device $device ) {
		if ( $this->findByUserId( $device->getUserId() ) === null ) {
			$this->insertDevice( $device );
		} else {
			$this->updateDevice( $device );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function findByPairToken( $pairToken ) {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$result =
			$dbr->select( self::TABLE_NAME, $this->FETCH_FIELDS,
				[ 'device_pair_token' => $pairToken ] );

		return $this->extractDevice( $result );
	}

	private function extractDevice( IResultWrapper $result ) {
		if ( $result->numRows() !== 1 ) {
			return null;
		}

		$device = $result->fetchObject();

		return new Device( (int)$device->device_user_id, $device->device_id, $device->id,
			$device->secret, $device->confirmed );
	}

	private function insertDevice( Device $device ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->insert( self::TABLE_NAME, [
			'device_user_id' => $device->getUserId(),
			'device_id' => $device->getDeviceId(),
			'device_pair_token' => $device->getPairToken(),
			'secret' => $device->getSecret(),
			'confirmed' => $device->isConfirmed(),
		] );
	}

	private function updateDevice( Device $device ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->update( self::TABLE_NAME, [
			'device_id' => $device->getDeviceId(),
			'secret' => $device->getSecret(),
			'device_pair_token' => null,
			'confirmed' => $device->isConfirmed(),
		], [
			'device_user_id' => $device->getUserId(),
		] );
	}
}

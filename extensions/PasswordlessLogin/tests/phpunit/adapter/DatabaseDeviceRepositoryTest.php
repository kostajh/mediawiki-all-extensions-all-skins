<?php

namespace PasswordlessLogin\adapter;

use MediaWiki\MediaWikiServices;
use MediaWikiTestCase;
use PasswordlessLogin\model\Device;
use User;

/**
 * @group Database
 */
class DatabaseDeviceRepositoryTest extends MediaWikiTestCase {
	/**
	 * @var DatabaseDeviceRepository
	 */
	private $repository;

	protected function setUp() : void {
		parent::setUp();
		$this->tablesUsed[] = 'passwordlesslogin_devices';

		$this->repository =
			new DatabaseDeviceRepository( MediaWikiServices::getInstance()->getDBLoadBalancer() );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::save
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::findByUserId
	 */
	public function testPersistsData() {
		$user = User::newFromName( 'UTSysop' );
		$device = new Device( $user->getId(), 'A_DEVICE_ID' );
		$device->confirm();
		$device->setSecret( 'A_SECRET' );

		$this->repository->save( $device );
		$result = $this->repository->findByUserId( $user->getId() );

		$this->assertEquals( $device->getDeviceId(), $result->getDeviceId() );
		$this->assertEquals( $device->getUserId(), $result->getUserId() );
		$this->assertEquals( $device->getSecret(), $result->getSecret() );
		$this->assertEquals( $device->isConfirmed(), $result->isConfirmed() );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::save
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::findByUserId
	 */
	public function testNoEntry() {
		$user = User::newFromName( 'UTSysop' );

		$result = $this->repository->findByUserId( $user->getId() );

		$this->assertNull( $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::save
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::remove
	 */
	public function testRemovesEntries() {
		$user = User::newFromName( 'UTSysop' );
		$device = new Device( $user->getId(), 'A_DEVICE_ID' );
		$this->repository->save( $device );

		$this->repository->remove( $user );

		$this->assertNull( $this->repository->findByUserId( $user->getId() ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::findByPairToken
	 */
	public function testFindByPairToken() {
		$device = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->repository->save( $device );

		$result = $this->repository->findByPairToken( $device->getPairToken() );

		$this->assertEquals( $device->getUserId(), $result->getUserId() );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseDeviceRepository::save
	 */
	public function testUpdatesExistingEntry() {
		$device = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->repository->save( $device );
		$device->setDeviceId( 'A_DEVICE_ID' );

		$this->repository->save( $device );
		$result = $this->repository->findByUserId( $device->getUserId() );

		$this->assertEquals( $device->getUserId(), $result->getUserId() );
		$this->assertEquals( 'A_DEVICE_ID', $result->getDeviceId() );
		$this->assertNull( $result->getPairToken() );
	}
}

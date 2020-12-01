<?php

namespace PasswordlessLogin\adapter;

use MediaWikiTestCase;
use PasswordlessLogin\model\Device;
use User;

class DeviceTest extends MediaWikiTestCase {
	/**
	 * @covers \PasswordlessLogin\model\Device::forUser
	 */
	public function testForUser() {
		$user = User::newFromName( 'UTSysop' );
		$user->setId( 1 );

		$device = Device::forUser( $user );

		$this->assertSame( 1, $device->getUserId() );
		$this->assertNotNull( $device->getPairToken() );
	}

	/**
	 * @covers \PasswordlessLogin\model\Device::__construct
	 */
	public function testConstructorEmptyPairToken() {
		$device = new Device( 1 );

		$this->assertNull( $device->getPairToken() );
	}

	/**
	 * @covers \PasswordlessLogin\model\Device::forUser
	 */
	public function testForUserUnique() {
		$user = User::newFromName( 'UTSysop' );
		$user->setId( 1 );

		$device = Device::forUser( $user );
		$secondDevice = Device::forUser( $user );

		$this->assertNotEquals( $device->getPairToken(), $secondDevice->getPairToken() );
	}

	/**
	 * @covers \PasswordlessLogin\model\Device::isConfirmed
	 */
	public function testIsNotConfirmed() {
		$device = new Device( 1 );

		$this->assertFalse( $device->isConfirmed() );
	}

	/**
	 * @covers \PasswordlessLogin\model\Device::isConfirmed
	 * @covers \PasswordlessLogin\model\Device::confirm
	 */
	public function testIsConfirmed() {
		$device = new Device( 1 );

		$device->confirm();

		$this->assertTrue( $device->isConfirmed() );
	}
}

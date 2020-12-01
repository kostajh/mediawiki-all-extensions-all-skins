<?php

namespace PasswordlessLogin\adapter\api;

use ApiTestCase;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\model\Device;
use PasswordlessLogin\model\DevicesRepository;
use User;

/**
 * @group API
 * @group Database
 * @group medium
 *
 * @covers \PasswordlessLogin\adapter\api\DeviceRegistrationTest
 */
class DeviceRegistrationTest extends ApiTestCase {
	/** @var DevicesRepository */
	private $deviceRepository;

	protected function setUp() : void {
		parent::setUp();
		$this->deviceRepository =
			MediaWikiServices::getInstance()->getService( DevicesRepository::SERVICE_NAME );
	}

	public function testInvalidPairToken() {
		$result = $this->doApiRequest( [
			'action' => 'passwordlesslogin',
			'pairToken' => 'invalid',
			'deviceId' => 'A_DEVICE_ID',
			'secret' => 'A_SECRET',
		] );

		$this->assertEquals( 'Failed', $result[0]['register']['result'] );
	}

	public function testFillsDeviceData() {
		$device = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->deviceRepository->save( $device );

		$result = $this->doApiRequest( [
			'action' => 'passwordlesslogin',
			'pairToken' => $device->getPairToken(),
			'deviceId' => 'DEVICE_ID',
			'secret' => urlencode( 'A+SECRET' ),
		] );

		$this->assertEquals( 'Success', $result[0]['register']['result'] );
		$registeredDevice = $this->deviceRepository->findByUserId( $device->getUserId() );
		$this->assertEquals( 'DEVICE_ID', $registeredDevice->getDeviceId() );
		$this->assertEquals( 'A+SECRET', $registeredDevice->getSecret() );
	}
}

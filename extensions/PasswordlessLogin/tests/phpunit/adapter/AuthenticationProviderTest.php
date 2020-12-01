<?php

namespace PasswordlessLogin\adapter;

use GlobalVarConfig;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Auth\PrimaryAuthenticationProvider;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use PasswordlessLogin\model\Challenge;
use PasswordlessLogin\model\ChallengesRepository;
use PasswordlessLogin\model\ConfirmRequest;
use PasswordlessLogin\model\Device;
use PasswordlessLogin\model\DevicesRepository;
use PasswordlessLogin\model\LinkRequest;
use PasswordlessLogin\model\LoginRequest;
use PasswordlessLogin\model\QRCodeRequest;
use PasswordlessLogin\model\RemoveRequest;
use PasswordlessLogin\model\VerifyRequest;
use StatusValue;
use User;
use WebRequest;

class AuthenticationProviderTest extends MediaWikiIntegrationTestCase {
	/**
	 * @var FakeDevicesRepository
	 */
	private $devicesRepository;
	/**
	 * @var FakeChallengesRepository
	 */
	private $challengesRepository;

	protected function setUp() : void {
		parent::setUp();

		$this->devicesRepository = new FakeDevicesRepository();
		$this->challengesRepository = new FakeChallengesRepository();
		$this->fakeFirebase = new FakeFirebase();
		$this->setService( DevicesRepository::SERVICE_NAME, $this->devicesRepository );
		$this->setService( ChallengesRepository::SERVICE_NAME, $this->challengesRepository );
		$this->setService( FirebaseMessageSender::SERVICE_NAME, $this->fakeFirebase );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::accountCreationType
	 */
	public function testAccountCreationType() {
		$provider = new AuthenticationProvider();

		$this->assertEquals( PrimaryAuthenticationProvider::TYPE_LINK,
			$provider->accountCreationType() );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::getAuthenticationRequests
	 */
	public function testGetAuthenticationRequestsLink() {
		$provider = new AuthenticationProvider();

		$this->assertEquals( [ new LinkRequest() ],
			$provider->getAuthenticationRequests( AuthManager::ACTION_LINK, [] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::getAuthenticationRequests
	 */
	public function testGetAuthenticationRequestsRemove() {
		$provider = new AuthenticationProvider();

		$this->assertEquals( [ new RemoveRequest() ],
			$provider->getAuthenticationRequests( AuthManager::ACTION_REMOVE, [] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::getAuthenticationRequests
	 */
	public function testGetAuthenticationRequestsLogin() {
		$provider = new AuthenticationProvider();

		$this->assertEquals( [ new LoginRequest() ],
			$provider->getAuthenticationRequests( AuthManager::ACTION_LOGIN, [] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testBeginPrimaryAuthentication() {
		$provider = new AuthenticationProvider();

		$this->assertEquals( AuthenticationResponse::newAbstain(),
			$provider->beginPrimaryAuthentication( [] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testBeginPrimaryAuthenticationNoDevice() {
		$provider = new AuthenticationProvider();
		$request = new LoginRequest();
		$request->username = 'UTSysop';

		$this->assertEquals( AuthenticationResponse::newAbstain(),
			$provider->beginPrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testBeginPrimaryAuthenticationNoConfirmedDevice() {
		$provider = new AuthenticationProvider();
		$request = new LoginRequest();
		$request->username = 'UTSysop';
		$this->devicesRepository->byUserId = Device::forUser( User::newFromName( 'UTSysop' ) );

		$this->assertEquals( AuthenticationResponse::newAbstain(),
			$provider->beginPrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testBeginPrimaryAuthenticationDevice() {
		$provider = new AuthenticationProvider();
		$request = new LoginRequest();
		$request->password = '';
		$request->username = 'UTSysop';
		$this->devicesRepository->byUserId = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->devicesRepository->byUserId->confirm();

		$this->assertEquals( AuthenticationResponse::newUI( [ new VerifyRequest() ],
			wfMessage( 'passwordlesslogin-verify-request' ) ),
			$provider->beginPrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testBeginPrimaryAuthenticationWithPassword() {
		$provider = new AuthenticationProvider();
		$request = new LoginRequest();
		$request->password = 'SOME_PASSWORD';
		$request->username = 'UTSysop';
		$this->devicesRepository->byUserId = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->devicesRepository->byUserId->confirm();

		$this->assertEquals( AuthenticationResponse::newAbstain(),
			$provider->beginPrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAuthentication
	 */
	public function testPublishUsernameForApiVerification() {
		$this->setMwGlobals( [ 'wgPLEnableApiVerification' => true ] );
		$provider = new AuthenticationProvider();
		$webRequest = new WebRequest();
		$provider->setManager( new AuthManager(
			$webRequest,
			new GlobalVarConfig(),
			MediaWikiServices::getInstance()->getObjectFactory(),
			MediaWikiServices::getInstance()->getPermissionManager(),
			MediaWikiServices::getInstance()->getHookContainer(),
			MediaWikiServices::getInstance()->getReadOnlyMode(),
			MediaWikiServices::getInstance()->getUserNameUtils(),
			MediaWikiServices::getInstance()->getBlockManager(),
			MediaWikiServices::getInstance()->getBlockErrorFormatter()
		) );
		$request = new LoginRequest();
		$request->password = '';
		$request->username = 'UTSysop';
		$this->devicesRepository->byUserId = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->devicesRepository->byUserId->confirm();

		$provider->beginPrimaryAuthentication( [ $request ] );

		$this->assertEquals( $this->challengesRepository->savedChallenge->getChallenge(),
			$webRequest->getSessionData( AuthenticationProvider::CHALLENGE_SESSION_KEY ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::continuePrimaryAuthentication
	 */
	public function testContinuePrimaryAuthenticationNoChallenge() {
		$provider = new AuthenticationProvider();
		$request = new VerifyRequest();
		$request->username = 'UTSysop';
		$this->challengesRepository->byUser = null;

		$this->assertEquals(
			AuthenticationResponse::newFail( wfMessage( 'passwordlesslogin-no-challenge' ) ),
			$provider->continuePrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::continuePrimaryAuthentication
	 */
	public function testContinuePrimaryAuthenticationNotVerified() {
		$provider = new AuthenticationProvider();
		$request = new VerifyRequest();
		$request->username = 'UTSysop';
		$this->challengesRepository->byUser = Challenge::forUser( User::newFromName( 'UTSysop' ) );

		$this->assertEquals( AuthenticationResponse::newUI( [ new VerifyRequest() ],
			wfMessage( 'passwordlesslogin-verification-pending' ) ),
			$provider->continuePrimaryAuthentication( [ $request ] ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::continuePrimaryAuthentication
	 */
	public function testContinuePrimaryAuthenticationSuccess() {
		$provider = new AuthenticationProvider();
		$request = new VerifyRequest();
		$request->username = 'UTSysop';
		$this->challengesRepository->byUser = Challenge::forUser( User::newFromName( 'UTSysop' ) );
		$this->challengesRepository->byUser->setSuccess( true );

		$this->assertEquals( AuthenticationResponse::newPass( 'UTSysop' ),
			$provider->continuePrimaryAuthentication( [ $request ] ) );
		$this->assertEquals( 'UTSysop', $this->challengesRepository->removed->getName() );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::testUserExists
	 */
	public function testTestUserExists() {
		$provider = new AuthenticationProvider();

		$this->assertFalse( $provider->testUserExists( 'A_USERNAME' ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::providerAllowsAuthenticationDataChange
	 */
	public function testProviderAllowsAuthenticationDataChangeNonPasswordlessLogin() {
		$provider = new AuthenticationProvider();

		$result = $provider->providerAllowsAuthenticationDataChange(
			$this->createMock( AuthenticationRequest::class ) );

		$this->assertEquals( StatusValue::newGood( 'ignored' ), $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::testUserExists
	 */
	public function testProviderAllowsAuthenticationDataChangeRemoveRequest() {
		$provider = new AuthenticationProvider();
		$this->devicesRepository->byUserId = new Device( '1' );
		$removeRequest = new RemoveRequest();
		$removeRequest->action = AuthManager::ACTION_REMOVE;
		$removeRequest->username = 'UTSysop';

		$result = $provider->providerAllowsAuthenticationDataChange( $removeRequest );

		$this->assertEquals( StatusValue::newGood(), $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::testUserExists
	 */
	public function testProviderAllowsAuthenticationDataChangeLinkRemoveRequest() {
		$provider = new AuthenticationProvider();
		$removeRequest = new RemoveRequest();
		$removeRequest->action = AuthManager::ACTION_LINK;

		$result = $provider->providerAllowsAuthenticationDataChange( $removeRequest );

		$this->assertEquals( StatusValue::newFatal( 'passwordlesslogin-error' ), $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::testUserExists
	 */
	public function testProviderAllowsAuthenticationDataChangeNoData() {
		$provider = new AuthenticationProvider();
		$this->devicesRepository->byUserId = null;
		$removeRequest = new RemoveRequest();
		$removeRequest->action = AuthManager::ACTION_REMOVE;
		$removeRequest->username = 'UTSysop';

		$result = $provider->providerAllowsAuthenticationDataChange( $removeRequest );

		$this->assertEquals( StatusValue::newFatal( 'passwordlesslogin-no-data' ), $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::providerChangeAuthenticationData
	 */
	public function testProviderChangeAuthenticationDataNoRemoveRequest() {
		$provider = new AuthenticationProvider();
		$request = new RemoveRequest();
		$request->action = AuthManager::ACTION_LINK;
		$request->username = 'UTSysop';

		$provider->providerChangeAuthenticationData( $request );

		$this->assertNull( $this->devicesRepository->removedFor );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::providerChangeAuthenticationData
	 */
	public function testProviderChangeAuthenticationDataRemoveRequest() {
		$provider = new AuthenticationProvider();
		$request = new RemoveRequest();
		$request->action = AuthManager::ACTION_REMOVE;
		$request->username = 'UTSysop';

		$provider->providerChangeAuthenticationData( $request );

		$this->assertEquals( User::newFromName( 'UTSysop' ), $this->devicesRepository->removedFor );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::providerChangeAuthenticationData
	 */
	public function testProviderChangeAuthenticationDataNoRemove() {
		$provider = new AuthenticationProvider();

		$provider->providerAllowsAuthenticationDataChange(
			$this->createMock( AuthenticationRequest::class ) );

		$this->assertNull( $this->devicesRepository->removedFor );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAccountLink
	 */
	public function testBeginPrimaryAccountLinkReturnsUI() {
		$provider = new AuthenticationProvider();

		$result =
			$provider->beginPrimaryAccountLink( User::newFromName( 'UTSysop' ),
				[ new LinkRequest() ] );

		$authenticationResponse = AuthenticationResponse::newUI(
			[ new QRCodeRequest( $this->devicesRepository->savedDevice->getPairToken() ) ],
			wfMessage( 'passwordlesslogin-pair-device-step' ) );
		$this->assertEquals( $authenticationResponse, $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAccountLink
	 */
	public function testContinuePrimaryAccountLinkNoDeviceReturnsUI() {
		$provider = new AuthenticationProvider();

		$result =
			$provider->continuePrimaryAccountLink( User::newFromName( 'UTSysop' ),
				[ new QRCodeRequest( 'A_PAIR_TOKEN' ) ] );

		$authenticationResponse =
			AuthenticationResponse::newUI( [ new QRCodeRequest( 'A_PAIR_TOKEN' ) ],
				wfMessage( 'passwordlesslogin-no-device-paired' ) );
		$this->assertEquals( $authenticationResponse, $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAccountLink
	 */
	public function testContinuePrimaryAccountLinkDeviceReturnsConfirmUI() {
		$provider = new AuthenticationProvider();
		$user = User::newFromName( 'UTSysop' );
		$this->devicesRepository->byUserId = Device::forUser( $user );
		$this->devicesRepository->byUserId->setDeviceId( "A_DEVICE_ID" );

		$result =
			$provider->continuePrimaryAccountLink( $user, [ new QRCodeRequest( 'A_PAIR_TOKEN' ) ] );

		$authenticationResponse =
			AuthenticationResponse::newUI( [ new ConfirmRequest() ],
				wfMessage( 'passwordlesslogin-verify-pair' ) );
		$this->assertEquals( $authenticationResponse, $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAccountLink
	 */
	public function testContinuePrimaryAccountLinkNotConfirmedUI() {
		$provider = new AuthenticationProvider();
		$user = User::newFromName( 'UTSysop' );
		$this->devicesRepository->byUserId = Device::forUser( $user );
		$this->challengesRepository->byUser = Challenge::forUser( $user );

		$result = $provider->continuePrimaryAccountLink( $user, [ new ConfirmRequest() ] );

		$authenticationResponse =
			AuthenticationResponse::newUI( [ new ConfirmRequest() ],
				wfMessage( 'passwordlesslogin-verify-pair' ) );
		$this->assertEquals( $authenticationResponse, $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\AuthenticationProvider::beginPrimaryAccountLink
	 */
	public function testContinuePrimaryAccountLinkConfirmedSuccess() {
		$provider = new AuthenticationProvider();
		$user = User::newFromName( 'UTSysop' );
		$this->devicesRepository->byUserId = Device::forUser( $user );
		$this->challengesRepository->byUser = Challenge::forUser( $user );
		$this->challengesRepository->byUser->setSuccess( true );

		$result = $provider->continuePrimaryAccountLink( $user, [ new ConfirmRequest() ] );

		$authenticationResponse = AuthenticationResponse::newPass();
		$this->assertEquals( $authenticationResponse, $result );
		$this->assertTrue( $this->devicesRepository->savedDevice->isConfirmed() );
	}
}

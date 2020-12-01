<?php

namespace PasswordlessLogin\adapter;

use Config;
use MediaWiki\Auth\AbstractPrimaryAuthenticationProvider;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\Hooks;
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

class AuthenticationProvider extends AbstractPrimaryAuthenticationProvider {
	const CHALLENGE_SOLVED = 'solved';
	const CHALLENGE_NO_CHALLENGE = 'noChallenge';
	const CHALLENGE_FAILED = 'failed';
	const CHALLENGE_SESSION_KEY = 'passwordlesslogin:username';

	/** @var DevicesRepository */
	private $devicesRepository;
	/** @var ChallengesRepository */
	private $challengesRepository;
	/** @var FirebaseMessageSender */
	private $firebaseMessageSender;
	/** @var Config */
	private $plConfig;

	public function __construct() {
		$mediaWikiServices = MediaWikiServices::getInstance();
		$this->devicesRepository =
			$mediaWikiServices->getService( DevicesRepository::SERVICE_NAME );
		$this->challengesRepository =
			$mediaWikiServices->getService( ChallengesRepository::SERVICE_NAME );
		$this->firebaseMessageSender =
			$mediaWikiServices->getService( FirebaseMessageSender::SERVICE_NAME );
		$this->plConfig = $mediaWikiServices->getConfigFactory()->makeConfig( 'passwordless' );
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthenticationRequests( $action, array $options ) {
		if ( $action === AuthManager::ACTION_REMOVE ) {
			return [ new RemoveRequest() ];
		}
		if ( $action === AuthManager::ACTION_LINK ) {
			return [ new LinkRequest() ];
		}
		if ( $action === AuthManager::ACTION_LOGIN ) {
			return [ new LoginRequest() ];
		}

		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function beginPrimaryAuthentication( array $reqs ) {
		$request = AuthenticationRequest::getRequestByClass( $reqs, LoginRequest::class );
		if ( $request === null ) {
			return AuthenticationResponse::newAbstain();
		}
		if ( $request->password !== '' ) {
			return AuthenticationResponse::newAbstain();
		}

		$user = User::newFromName( $request->username );
		$device = $this->devicesRepository->findByUserId( $user->getId() );
		if ( $device == null || !$device->isConfirmed() ) {
			return AuthenticationResponse::newAbstain();
		}
		$challenge = $this->newChallenge( $user, $device );

		Hooks::$addFrontendModules = true;

		if ( $this->plConfig->get( 'PLEnableApiVerification' ) ) {
			$this->manager->getRequest()
				->setSessionData( self::CHALLENGE_SESSION_KEY, $challenge->getChallenge() );
		}

		return AuthenticationResponse::newUI( [ new VerifyRequest() ],
			wfMessage( 'passwordlesslogin-verify-request' ) );
	}

	private function newChallenge( User $user, Device $device ) {
		$this->challengesRepository->remove( $user );
		$challenge = Challenge::forUser( $user );
		$this->challengesRepository->save( $challenge );
		$this->firebaseMessageSender->send( $device, $challenge );

		return $challenge;
	}

	/**
	 * @inheritDoc
	 */
	public function continuePrimaryAuthentication( array $reqs ) {
		/** @var VerifyRequest $request */
		$request = AuthenticationRequest::getRequestByClass( $reqs, VerifyRequest::class );
		if ( $request === null ) {
			return AuthenticationResponse::newFail(
				wfMessage( 'passwordlesslogin-error-no-authentication-workflow' ) );
		}
		$user = User::newFromName( $request->username );
		switch ( $this->isChallengeSolved( $user ) ) {
			case self::CHALLENGE_NO_CHALLENGE:
				return AuthenticationResponse::newFail(
					wfMessage( 'passwordlesslogin-no-challenge' ) );
			case self::CHALLENGE_FAILED:
				Hooks::$addFrontendModules = true;

				return AuthenticationResponse::newUI( [ new VerifyRequest() ],
					wfMessage( 'passwordlesslogin-verification-pending' ) );
			case self::CHALLENGE_SOLVED:
				return AuthenticationResponse::newPass( $user->getName() );
		}

		return AuthenticationResponse::newAbstain();
	}

	private function isChallengeSolved( User $user ) {
		$challenge = $this->challengesRepository->findByUser( $user );
		if ( $challenge === null ) {
			return self::CHALLENGE_NO_CHALLENGE;
		}
		if ( $challenge->getSuccess() === false ) {
			return self::CHALLENGE_FAILED;
		}

		$this->challengesRepository->remove( $user );

		return self::CHALLENGE_SOLVED;
	}

	/**
	 * @inheritDoc
	 */
	public function beginPrimaryAccountLink( $user, array $reqs ) {
		$device = Device::forUser( $user );
		$this->devicesRepository->remove( $user );
		$this->devicesRepository->save( $device );

		return AuthenticationResponse::newUI( [ new QRCodeRequest( $device->getPairToken() ) ],
			wfMessage( 'passwordlesslogin-pair-device-step' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function continuePrimaryAccountLink( $user, array $reqs ) {
		/** @var QRCodeRequest $request */
		$request = AuthenticationRequest::getRequestByClass( $reqs, QRCodeRequest::class );
		if ( $request !== null ) {
			$device = $this->devicesRepository->findByUserId( $user->getId() );
			if ( $device == null || $device->getDeviceId() == null ) {
				return AuthenticationResponse::newUI( [ $request ],
					wfMessage( 'passwordlesslogin-no-device-paired' ) );
			}

			$this->newChallenge( $user, $device );

			return AuthenticationResponse::newUI( [ new ConfirmRequest() ],
				wfMessage( 'passwordlesslogin-verify-pair' ) );
		}

		/** @var ConfirmRequest $request */
		$request = AuthenticationRequest::getRequestByClass( $reqs, ConfirmRequest::class );
		if ( $request !== null ) {
			switch ( $this->isChallengeSolved( $user ) ) {
				case self::CHALLENGE_NO_CHALLENGE:
					return AuthenticationResponse::newFail( wfMessage( 'passwordlesslogin-no-challenge' ) );
				case self::CHALLENGE_FAILED:
					return AuthenticationResponse::newUI( $reqs,
						wfMessage( 'passwordlesslogin-verify-pair' ) );
				case self::CHALLENGE_SOLVED:
					$device = $this->devicesRepository->findByUserId( $user->getId() );
					if ( $device == null ) {
						return AuthenticationResponse::newUI( [ $request ],
							wfMessage( 'passwordlesslogin-no-device-paired' ) );
					}
					$device->confirm();
					$this->devicesRepository->save( $device );

					return AuthenticationResponse::newPass();
			}
		}

		return AuthenticationResponse::newFail(
			wfMessage( 'passwordlesslogin-error-no-authentication-workflow' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function testUserExists( $username, $flags = User::READ_NORMAL ) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function providerAllowsAuthenticationDataChange(
		AuthenticationRequest $req, $checkData = true
	) {
		if ( get_class( $req ) !== RemoveRequest::class ) {
			return StatusValue::newGood( 'ignored' );
		}

		if ( $req->action !== AuthManager::ACTION_REMOVE ) {
			return StatusValue::newFatal( 'passwordlesslogin-error' );
		}

		$user = User::newFromName( $req->username );
		if ( $user === false || $this->devicesRepository->findByUserId( $user->getId() ) == null ) {
			return StatusValue::newFatal( 'passwordlesslogin-no-data' );
		}

		return StatusValue::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function providerChangeAuthenticationData( AuthenticationRequest $req ) {
		if ( get_class( $req ) !== RemoveRequest::class ) {
			return;
		}
		if ( $req->action !== AuthManager::ACTION_REMOVE ) {
			return;
		}
		$user = User::newFromName( $req->username );
		if ( $user === false ) {
			return;
		}
		$this->devicesRepository->remove( $user );
	}

	/**
	 * @inheritDoc
	 */
	public function accountCreationType() {
		return self::TYPE_LINK;
	}

	/**
	 * @inheritDoc
	 */
	public function beginPrimaryAccountCreation( $user, $creator, array $reqs ) {
		return AuthenticationResponse::newAbstain();
	}
}

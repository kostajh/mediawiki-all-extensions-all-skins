<?php

namespace MediaWiki\Extensions\EmailAuth;

use Hooks;
use MediaWiki\Auth\AbstractSecondaryAuthenticationProvider;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Logger\LoggerFactory;
use Message;
use MWCryptRand;
use User;

class EmailAuthSecondaryAuthenticationProvider extends AbstractSecondaryAuthenticationProvider {
	/** Fail the login attempt after this many retries */
	const RETRIES = 3;

	public function getAuthenticationRequests( $action, array $options ) {
		return [];
	}

	public function beginSecondaryAuthentication( $user, array $reqs ) {
		$token = MWCryptRand::generateHex( 6 );
		$messages = $this->runEmailAuthRequireToken( $user, $token );
		if ( !$messages ) {
			return AuthenticationResponse::newPass();
		}
		/** @var Message $formMessage */
		/** @var Message $subjectMessage */
		/** @var Message $bodyMessage */
		list( $formMessage, $subjectMessage, $bodyMessage ) = $messages;

		LoggerFactory::getInstance( 'EmailAuth' )->info( 'Verification requested for {user}', [
			'user' => $user->getName(),
			'ip' => $user->getRequest()->getIP(),
			'formMessageKey' => $formMessage->getKey(),
			'subjectMessageKey' => $subjectMessage->getKey(),
			'bodyMessageKey' => $bodyMessage->getKey(),
		] );

		$this->manager->setAuthenticationSessionData( 'EmailAuthToken', $token );
		$this->manager->setAuthenticationSessionData( 'EmailAuthFailures', 0 );
		$user->sendMail( $subjectMessage, $bodyMessage );
		return AuthenticationResponse::newUI( [ new EmailAuthAuthenticationRequest() ], $formMessage );
	}

	public function continueSecondaryAuthentication( $user, array $reqs ) {
		$token = $this->manager->getAuthenticationSessionData( 'EmailAuthToken' );
		/** @var EmailAuthAuthenticationRequest $req */
		$req = AuthenticationRequest::getRequestByClass( $reqs, EmailAuthAuthenticationRequest::class );
		if ( $req && hash_equals( $token, $req->token ) ) {
			LoggerFactory::getInstance( 'EmailAuth' )->info( 'Successful verification for {user}', [
				'user' => $user->getName(),
				'ip' => $user->getRequest()->getIP(),
			] );
			return AuthenticationResponse::newPass();
		} elseif ( $req && $req->token ) {
			// do not log if the code is simply missing - accidental enter or confused bot
			LoggerFactory::getInstance( 'EmailAuth' )->info( 'Failed verification for {user}', [
				'user' => $user->getName(),
				'ip' => $user->getRequest()->getIP(),
			] );
		}

		$failures = $this->manager->getAuthenticationSessionData( 'EmailAuthFailures' );
		if ( $failures >= self::RETRIES ) {
				return AuthenticationResponse::newFail( wfMessage( 'emailauth-login-retry-limit' ) );
		}
		$this->manager->setAuthenticationSessionData( 'EmailAuthFailures', $failures + 1 );
		return AuthenticationResponse::newUI( [ new EmailAuthAuthenticationRequest() ],
			wfMessage( 'emailauth-login-failure' ), 'error' );
	}

	public function beginSecondaryAccountCreation( $user, $creator, array $reqs ) {
		return AuthenticationResponse::newAbstain();
	}

	/**
	 * @param User $user
	 * @param string $token
	 * @return Message[]|bool [ form message, email subject, email body ] or false if no
	 *   verification should happen
	 */
	protected function runEmailAuthRequireToken( User $user, $token ) {
		global $wgSitename;

		if ( !$user->isEmailConfirmed() ) { // nothing we can do
			return false;
		}

		$verificationRequired = false;
		$formMessage = wfMessage( 'emailauth-login-message', $user->getEmail() );
		$subjectMessage = wfMessage( 'emailauth-email-subject', $wgSitename );
		$bodyMessage = wfMessage( 'emailauth-email-body', $wgSitename );

		Hooks::run( 'EmailAuthRequireToken', [ $user, &$verificationRequired, &$formMessage,
			&$subjectMessage, &$bodyMessage ] );
		$bodyMessage->params( $token );

		return $verificationRequired ? [ $formMessage, $subjectMessage, $bodyMessage ] :
			false;
	}
}

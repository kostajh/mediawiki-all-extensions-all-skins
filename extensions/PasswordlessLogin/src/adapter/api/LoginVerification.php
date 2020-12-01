<?php

namespace PasswordlessLogin\adapter\api;

use ApiBase;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\adapter\AuthenticationProvider;
use PasswordlessLogin\model\ChallengesRepository;

class LoginVerification extends ApiBase {
	const MAX_RETRIES = 5;

	/** @var ChallengesRepository */
	private $challengesRepository;

	public function execute() {
		$this->challengesRepository =
			MediaWikiServices::getInstance()->getService( ChallengesRepository::SERVICE_NAME );

		$challengeKey =
			$this->getRequest()->getSessionData( AuthenticationProvider::CHALLENGE_SESSION_KEY );
		if ( $challengeKey === null ) {
			$this->failed();

			return;
		}
		$challenge = $this->challengesRepository->findByChallenge( $challengeKey );
		if ( $challenge === null ) {
			$this->failed();

			return;
		}

		$retries = 0;
		while ( $this->isPending( $challengeKey ) && $retries < self::MAX_RETRIES ) {
			$retries++;
			sleep( 2 );
		}
		if ( $this->isPending( $challengeKey ) ) {
			$this->failed();
		} else {
			$this->getResult()->addValue( null, 'login-verification', [
				'result' => 'Success',
			] );
		}
	}

	private function failed(): void {
		$this->getResult()->addValue( null, 'login-verification', [
			'result' => 'Failed',
		] );
	}

	private function isPending( $challengeKey ) {
		$challenge = $this->challengesRepository->findByChallenge( $challengeKey );

		return !$challenge->getSuccess();
	}
}

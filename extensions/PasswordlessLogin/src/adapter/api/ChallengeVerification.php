<?php

namespace PasswordlessLogin\adapter\api;

use ApiBase;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\model\ChallengesRepository;
use PasswordlessLogin\model\DevicesRepository;

class ChallengeVerification extends ApiBase {
	public function execute() {
		/** @var ChallengesRepository $challengesRepository */
		$challengesRepository =
			MediaWikiServices::getInstance()->getService( ChallengesRepository::SERVICE_NAME );
		/** @var DevicesRepository $devicesRepository */
		$devicesRepository =
			MediaWikiServices::getInstance()->getService( DevicesRepository::SERVICE_NAME );

		$this->requirePostedParameters( [ 'challenge', 'response' ] );
		$params = $this->extractRequestParams();
		$challenge = $params['challenge'];
		$response = $params['response'];

		$challenge = $challengesRepository->findByChallenge( $challenge );
		if ( $challenge == null ) {
			$this->getResult()->addValue( null, 'verify', [
				'result' => 'Failed',
			] );

			return;
		}
		$device = $devicesRepository->findByUserId( $challenge->getUserId() );

		if ( $device->getSecret() === null ) {
			$this->getResult()->addValue( null, 'verify', [
				'result' => 'Failed',
			] );

			return;
		}

		if ( !$challenge->verify( $device, $response ) ) {
			$this->getResult()->addValue( null, 'verify', [
				'result' => 'Failed',
			] );

			return;
		}

		$challengesRepository->save( $challenge );
		$this->getResult()->addValue( null, 'verify', [
			'result' => 'Success',
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			'challenge' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'response' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}
}

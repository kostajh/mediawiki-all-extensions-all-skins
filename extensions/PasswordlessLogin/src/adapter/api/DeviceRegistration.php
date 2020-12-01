<?php

namespace PasswordlessLogin\adapter\api;

use ApiBase;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\model\DevicesRepository;

class DeviceRegistration extends ApiBase {
	public function execute() {
		/** @var DevicesRepository $devicesRepository */
		$devicesRepository =
			MediaWikiServices::getInstance()->getService( DevicesRepository::SERVICE_NAME );

		$this->requirePostedParameters( [ 'pairToken', 'deviceId', 'secret' ] );
		$params = $this->extractRequestParams();
		$pairToken = $params['pairToken'];
		$deviceId = $params['deviceId'];
		$secret = rawurldecode( $params['secret'] );

		$device = $devicesRepository->findByPairToken( $pairToken );
		if ( $device == null ) {
			$this->getResult()->addValue( null, 'register', [
				'result' => 'Failed',
			] );

			return;
		}

		$device->setDeviceId( $deviceId );
		$device->setSecret( $secret );
		$devicesRepository->save( $device );

		$this->getResult()->addValue( null, 'register', [
			'result' => 'Success',
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			'pairToken' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'deviceId' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'secret' => [
				ApiBase::PARAM_TYPE => 'password',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}
}

<?php

namespace BlueSpice\UserInfo\Api;

use BlueSpice\Api;
use BlueSpice\Api\Task\StatusConverter;
use Status;
use User;

class Meta extends Api {

	public const PARAM_USERNAME = 'username';

	public function execute() {
		$this->checkPermissions();

		$user = User::newFromName( $this->getParameter( 'username' ) );
		if ( !$user ) {
			return Status::newFatal( 'invalid user' );
		}
		$factory = $this->getServices()->getService(
			'BSUserInfoMetaDataFactory'
		);
		$return = [
			'username' => $user->getName(),
			'meta' => []
		];
		foreach ( $factory->getAllKeys() as $name ) {
			$metaData = $factory->factory( $name, $user );
			if ( $metaData === false ) {
				continue;
			}
			if ( $metaData->isHidden() ) {
				continue;
			}
			$return['meta'][$metaData->getName()] = $metaData->toArray();
		}
		$status = Status::newGood( $return );
		$api = $this;
		$converter = new StatusConverter( $api, $status );
		$converter->convert();
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			static::PARAM_USERNAME => [
				static::PARAM_REQUIRED => true,
				static::PARAM_TYPE => 'string',
				static::PARAM_HELP_MSG => 'apihelp-bs-userinfometa-param-username',
			],
		];
	}

}

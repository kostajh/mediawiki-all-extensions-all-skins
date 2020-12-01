<?php

namespace PasswordlessLogin\model;

use MediaWiki\Auth\AuthenticationRequest;

class RemoveRequest extends AuthenticationRequest {
	/**
	 * @inheritDoc
	 */
	public function describeCredentials() {
		return [
			'provider' => wfMessage( 'passwordlesslogin' ),
			'account' => wfMessage( 'passwordlesslogin-authentication-app' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getFieldInfo() {
		return [];
	}
}

<?php

namespace PasswordlessLogin\model;

use MediaWiki\Auth\AuthenticationRequest;

class LoginRequest extends AuthenticationRequest {
	/** @var string Password */
	public $password = null;

	/**
	 * @inheritDoc
	 */
	public function getFieldInfo() {
		return [
			'username' => [
				'type' => 'string',
				'help' => wfMessage( 'authmanager-username-help' ),
			],
			'password' => [
				'type' => 'password',
				'optional' => true,
			],
		];
	}
}

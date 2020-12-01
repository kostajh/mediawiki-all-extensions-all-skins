<?php

namespace PasswordlessLogin\model;

use MediaWiki\Auth\AuthenticationRequest;

class VerifyRequest extends AuthenticationRequest {
	/**
	 * @inheritDoc
	 */
	public function getFieldInfo() {
		return [
			'request' => [
				'type' => 'hidden',
				'value' => 'hidden'
			]
		];
	}
}

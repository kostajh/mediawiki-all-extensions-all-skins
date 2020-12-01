<?php

namespace PasswordlessLogin\model;

use MediaWiki\Auth\AuthenticationRequest;

class ConfirmRequest extends AuthenticationRequest {
	/**
	 * @inheritDoc
	 */
	public function getFieldInfo() {
		return [
			'confirm' => [
				'type' => 'hidden',
				'value' => 'hidden'
			]
		];
	}
}

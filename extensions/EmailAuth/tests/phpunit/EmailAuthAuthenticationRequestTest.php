<?php

namespace MediaWiki\Extensions\EmailAuth;

use MediaWiki\Auth\AuthenticationRequestTestCase;

/**
 * @covers \MediaWiki\Extensions\EmailAuth\EmailAuthAuthenticationRequest
 */
class EmailAuthAuthenticationRequestTest extends AuthenticationRequestTestCase {
	protected function getInstance( array $args = [] ) {
		return new EmailAuthAuthenticationRequest();
	}

	public function provideLoadFromSubmission() {
		return [
			[ [], [], false ],
			[ [], [ 'token' => 'foo' ], [ 'token' => 'foo' ] ],
		];
	}
}

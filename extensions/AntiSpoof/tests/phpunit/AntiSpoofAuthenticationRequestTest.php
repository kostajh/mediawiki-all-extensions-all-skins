<?php

use MediaWiki\Auth\AuthenticationRequestTestCase;

/**
 * @covers AntiSpoofAuthenticationRequest
 */
class AntiSpoofAuthenticationRequestTest extends AuthenticationRequestTestCase {

	protected function getInstance( array $args = [] ) {
		return new AntiSpoofAuthenticationRequest();
	}

	public function provideLoadFromSubmission() {
		return [
			'empty' => [ [], [], [ 'ignoreAntiSpoof' => false ] ],
			'true' => [ [], [ 'ignoreAntiSpoof' => '1' ], [ 'ignoreAntiSpoof' => true ] ],
		];
	}
}

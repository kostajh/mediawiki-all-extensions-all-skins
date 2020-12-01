<?php

use MediaWiki\Extensions\TrustedXFF\TrustedXFF;

class TrustedXFFBodyTest extends MediaWikiTestCase {

	protected function setUp() : void {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	/**
	 * @covers MediaWiki\Extensions\TrustedXFF\TrustedXFF::isTrusted
	 */
	public function testMissingXffFileIsHandledGracefully() {
		$ip = '127.0.0.2';
		$trusted = false;

		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertFalse( $trusted );
	}

}

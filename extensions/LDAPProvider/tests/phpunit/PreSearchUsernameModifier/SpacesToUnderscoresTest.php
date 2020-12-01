<?php

namespace MediaWiki\Extension\LDAPProvider\Tests\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\SpacesToUnderscores;
use PHPUnit\Framework\TestCase;

class SpacesToUnderscoresTest extends TestCase {

	/**
	 * @covers SpacesToUnderscores::modify
	 * @return void
	 */
	public function testModify() {
		$modifier = new SpacesToUnderscores();

		$modifiedUsername = $modifier->modify( 'Some User' );

		$this->assertEquals( 'Some_User', $modifiedUsername );
	}

	/**
	 * @covers SpacesToUnderscores::factory
	 * @return void
	 */
	public function testFactory() {
		$modifier = SpacesToUnderscores::newInstance();

		$this->assertInstanceOf( SpacesToUnderscores::class, $modifier );
	}
}

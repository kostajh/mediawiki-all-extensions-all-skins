<?php

namespace MediaWiki\Extension\LDAPProvider\Tests\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\ToLower;
use PHPUnit\Framework\TestCase;

class ToLowerTest extends TestCase {

	/**
	 * @covers ToLower::modify
	 * @return void
	 */
	public function testModify() {
		$modifier = new ToLower();

		$modifiedUsername = $modifier->modify( 'Some User' );

		$this->assertEquals( 'some user', $modifiedUsername );
	}

	/**
	 * @covers ToLower::factory
	 * @return void
	 */
	public function testFactory() {
		$modifier = ToLower::newInstance();

		$this->assertInstanceOf( ToLower::class, $modifier );
	}
}

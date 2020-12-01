<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWiki\WikispeechSpeechDataCollector\Domain\User;
use MediaWikiUnitTestCase;

/**
 * Class UserTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\User
 * @since 0.1.0
 */
class UserTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitUser(
				User &$user
			): ?object {
				return null;
			}
		};
		$instance = new User();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

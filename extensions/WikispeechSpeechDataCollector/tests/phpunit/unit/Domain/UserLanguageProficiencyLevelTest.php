<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserLanguageProficiencyLevel;
use MediaWikiUnitTestCase;

/**
 * Class UserLanguageProficiencyLevelTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\UserLanguageProficiencyLevel
 * @since 0.1.0
 */
class UserLanguageProficiencyLevelTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitUserLanguageProficiencyLevel(
				UserLanguageProficiencyLevel &$userLanguageProficiencyLevel
			): ?object {
				return null;
			}
		};
		$instance = new UserLanguageProficiencyLevel();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

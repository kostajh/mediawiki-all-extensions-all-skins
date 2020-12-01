<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Recording;
use MediaWikiUnitTestCase;

/**
 * Class RecordingTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\Recording
 * @since 0.1.0
 */
class RecordingTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitRecording(
				Recording &$recording
			): ?object {
				return null;
			}
		};
		$instance = new Recording();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

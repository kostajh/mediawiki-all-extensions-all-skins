<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation;
use MediaWikiUnitTestCase;

/**
 * Class RecordingAnnotationTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation
 * @since 0.1.0
 */
class RecordingAnnotationTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitRecordingAnnotation(
				RecordingAnnotation &$recordingAnnotation
			): ?object {
				return null;
			}
		};
		$instance = new RecordingAnnotation();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

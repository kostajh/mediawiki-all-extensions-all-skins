<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript;
use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWikiUnitTestCase;

/**
 * Class ManuscriptTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript
 * @since 0.1.0
 */
class ManuscriptTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitManuscript( Manuscript &$manuscript ): ?object {
				return null;
			}
		};
		$instance = new Manuscript();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

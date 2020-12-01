<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptPrompt;
use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitorAdapter;
use MediaWikiUnitTestCase;

/**
 * Class ManuscriptPromptTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit\Domain
 * @covers \MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptPrompt
 * @since 0.1.0
 */
class ManuscriptPromptTest extends MediaWikiUnitTestCase {

	/**
	 * Asserts that the visitor accept function
	 * invokes the correct function in the visitor implementation.
	 */
	public function testAcceptVisitor() {
		// The abstract PersistentVisitorAdapter throws an exception on all invocations.
		$visitor = new class extends PersistentVisitorAdapter {
			public function visitManuscriptPrompt( ManuscriptPrompt &$manuscriptPrompt ): ?object {
				return null;
			}
		};
		$instance = new ManuscriptPrompt();
		$instance->accept( $visitor );
		// no exception should have been thrown
		$this->assertTrue( true );
	}

}

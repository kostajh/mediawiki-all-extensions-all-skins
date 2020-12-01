<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\SkippedManuscriptPrompt;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class SkippedManuscriptPromptEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param SkippedManuscriptPrompt $expected
	 * @param SkippedManuscriptPrompt $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsTimestampSame( 'skipped', $expected->getSkipped(), $actual->getSkipped() );
		$this->matchIsSame( 'user', $expected->getUser(), $actual->getUser() );
		$this->matchIsSame( 'manuscriptPrompt',
			$expected->getManuscriptPrompt(), $actual->getManuscriptPrompt() );
	}

}

<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Recording;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class RecordingEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param Recording $expected
	 * @param Recording $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsTimestampSame( 'recorded', $expected->getRecorded(), $actual->getRecorded() );
		$this->matchIsSame( 'voiceOf', $expected->getVoiceOf(), $actual->getVoiceOf() );
		$this->matchIsSame( 'spokenDialect',
			$expected->getSpokenDialect(), $actual->getSpokenDialect() );
		$this->matchIsSame( 'manuscriptPrompt',
			$expected->getManuscriptPrompt(), $actual->getManuscriptPrompt() );
	}

}

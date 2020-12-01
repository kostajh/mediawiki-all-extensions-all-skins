<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class RecordingAnnotationEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param RecordingAnnotation $expected
	 * @param RecordingAnnotation $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'recording', $expected->getRecording(), $actual->getRecording() );
		$this->matchIsSame( 'start', $expected->getStart(), $actual->getStart() );
		$this->matchIsSame( 'end',	$expected->getEnd(), $actual->getEnd() );
		$this->matchIsSame( 'stereotype', $expected->getStereotype(), $actual->getStereotype() );
		$this->matchIsSame( 'value', $expected->getValue(), $actual->getValue() );
	}

}

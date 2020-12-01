<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotationStereotype;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class RecordingAnnotationStereotypeEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param RecordingAnnotationStereotype $expected
	 * @param RecordingAnnotationStereotype $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'valueClass', $expected->getValueClass(), $actual->getValueClass() );
		$this->matchIsSame( 'description',	$expected->getDescription(), $actual->getDescription() );
	}

}

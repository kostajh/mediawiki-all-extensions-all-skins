<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Language;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript;
use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptDomain;
use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptPrompt;
use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitor;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Recording;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotationStereotype;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingReview;
use MediaWiki\WikispeechSpeechDataCollector\Domain\SkippedManuscriptPrompt;
use MediaWiki\WikispeechSpeechDataCollector\Domain\User;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserDialect;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserLanguageProficiencyLevel;

/**
 * Sets all nullable values to null in the visited instance.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class PersistentSetNullableNull implements PersistentVisitor {

	/**
	 * @param Language &$language
	 * @return null
	 */
	public function visitLanguage(
		Language &$language
	) {
		$language->setIso639a1( null );
		$language->setIso639a2b( null );
		$language->setIso639a2t( null );
		$language->setIso639a3( null );
		return null;
	}

	/**
	 * @param Manuscript &$manuscript
	 * @return null
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	) {
		$manuscript->setDomain( null );
		$manuscript->setDisabled( null );
		return null;
	}

	/**
	 * @param ManuscriptDomain &$manuscriptDomain
	 * @return null
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	) {
		$manuscriptDomain->setParent( null );
		return null;
	}

	/**
	 * @param ManuscriptPrompt &$manuscriptPrompt
	 * @return null
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	) {
		return null;
	}

	/**
	 * @param Recording &$recording
	 * @return null
	 */
	public function visitRecording(
		Recording &$recording
	) {
		$recording->setSpokenDialect( null );
		return null;
	}

	/**
	 * @param RecordingAnnotation &$recordingAnnotation
	 * @return null
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	) {
		$recordingAnnotation->setValue( null );
		return null;
	}

	/**
	 * @param RecordingAnnotationStereotype &$recordingAnnotationStereotype
	 * @return null
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	) {
		return null;
	}

	/**
	 * @param RecordingReview &$recordingReview
	 * @return null
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	) {
		return null;
	}

	/**
	 * @param SkippedManuscriptPrompt &$skippedManuscriptPrompt
	 * @return null
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	) {
		return null;
	}

	/**
	 * @param User &$user
	 * @return null
	 */
	public function visitUser(
		User &$user
	) {
		$user->setMediaWikiUser( null );
		$user->setYearBorn( null );
		return null;
	}

	/**
	 * @param UserDialect &$userDialect
	 * @return null
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	) {
		return null;
	}

	/**
	 * @param UserLanguageProficiencyLevel &$languageProficiencyLevel
	 * @return null
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	) {
		return null;
	}
}

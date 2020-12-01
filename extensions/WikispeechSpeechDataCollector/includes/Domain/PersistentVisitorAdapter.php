<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

use Exception;

/**
 * Class PersistentVisitorAdapter
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 *
 * An adapter pattern with no actual implementations on top of the persistent visitor.
 * For use with those implementations that only add support for some of the persistent subclasses.
 *
 * Throws an exception on any non overridden invocation.
 *
 * @since 0.1.0
 */
abstract class PersistentVisitorAdapter implements PersistentVisitor {

	/**
	 * @inheritDoc
	 */
	public function visitLanguage(
		Language &$language
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitRecording(
		Recording &$recording
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitUser(
		User &$user
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	) {
		throw new Exception( 'Not implemented' );
	}

	/**
	 * @inheritDoc
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	) {
		throw new Exception( 'Not implemented' );
	}
}

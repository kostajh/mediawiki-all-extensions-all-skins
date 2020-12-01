<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Interface PersistentVisitor
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 *
 * https://en.wikipedia.org/wiki/Visitor_pattern
 * @since 0.1.0
 */
interface PersistentVisitor {
	/**
	 * @param Language &$language
	 * @return mixed|null
	 */
	public function visitLanguage(
		Language &$language
	);

	/**
	 * @param Manuscript &$manuscript
	 * @return mixed|null
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	);

	/**
	 * @param ManuscriptDomain &$manuscriptDomain
	 * @return mixed|null
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	);

	/**
	 * @param ManuscriptPrompt &$manuscriptPrompt
	 * @return mixed|null
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	);

	/**
	 * @param Recording &$recording
	 * @return mixed|null
	 */
	public function visitRecording(
		Recording &$recording
	);

	/**
	 * @param RecordingAnnotation &$recordingAnnotation
	 * @return mixed|null
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	);

	/**
	 * @param RecordingAnnotationStereotype &$recordingAnnotationStereotype
	 * @return mixed|null
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	);

	/**
	 * @param RecordingReview &$recordingReview
	 * @return mixed|null
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	);

	/**
	 * @param SkippedManuscriptPrompt &$skippedManuscriptPrompt
	 * @return mixed|null
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	);

	/**
	 * @param User &$user
	 * @return mixed|null
	 */
	public function visitUser(
		User &$user
	);

	/**
	 * @param UserDialect &$userDialect
	 * @return mixed|null
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	);

	/**
	 * @param UserLanguageProficiencyLevel &$languageProficiencyLevel
	 * @return mixed|null
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	);
}

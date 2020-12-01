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
 * For use when asserting that two instance of {@link Persistent} equals.
 *
 * This is a {@link PersistentVisitor} that when accepted using
 * {@link Persistent::accept()} will return a PHPUnit constraint
 * that can be asserted using {@link Assert::assertThat()}.
 *
 * Typical use:
 * <code>
 * $this->assertThat(
 *   $expectedInstance,
 *   $testedInstance->accept( new PersistentEqualsConstraintFactory()
 * );
 * </code>
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests
 * @since 0.1.0
 */
class PersistentEqualsConstraintFactory implements PersistentVisitor {

	/**
	 * @since 0.1.0
	 * @param Language &$language
	 * @return LanguageEqualsConstraint
	 */
	public function visitLanguage(
		Language &$language
	): LanguageEqualsConstraint {
		return new LanguageEqualsConstraint( $language );
	}

	/**
	 * @since 0.1.0
	 * @param Manuscript &$manuscript
	 * @return ManuscriptEqualsConstraint
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	): ManuscriptEqualsConstraint {
		return new ManuscriptEqualsConstraint( $manuscript );
	}

	/**
	 * @since 0.1.0
	 * @param ManuscriptDomain &$manuscriptDomain
	 * @return ManuscriptDomainEqualsConstraint
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	): ManuscriptDomainEqualsConstraint {
		return new ManuscriptDomainEqualsConstraint( $manuscriptDomain );
	}

	/**
	 * @since 0.1.0
	 * @param ManuscriptPrompt &$manuscriptPrompt
	 * @return ManuscriptPromptEqualsConstraint
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	): ManuscriptPromptEqualsConstraint {
		return new ManuscriptPromptEqualsConstraint( $manuscriptPrompt );
	}

	/**
	 * @since 0.1.0
	 * @param Recording &$recording
	 * @return RecordingEqualsConstraint
	 */
	public function visitRecording(
		Recording &$recording
	): RecordingEqualsConstraint {
		return new RecordingEqualsConstraint( $recording );
	}

	/**
	 * @since 0.1.0
	 * @param RecordingAnnotation &$recordingAnnotation
	 * @return RecordingAnnotationEqualsConstraint
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	): RecordingAnnotationEqualsConstraint {
		return new RecordingAnnotationEqualsConstraint( $recordingAnnotation );
	}

	/**
	 * @since 0.1.0
	 * @param RecordingAnnotationStereotype &$recordingAnnotationStereotype
	 * @return RecordingAnnotationStereotypeEqualsConstraint
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	): RecordingAnnotationStereotypeEqualsConstraint {
		return new RecordingAnnotationStereotypeEqualsConstraint( $recordingAnnotationStereotype );
	}

	/**
	 * @since 0.1.0
	 * @param RecordingReview &$recordingReview
	 * @return RecordingReviewEqualsConstraint
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	): RecordingReviewEqualsConstraint {
		return new RecordingReviewEqualsConstraint( $recordingReview );
	}

	/**
	 * @since 0.1.0
	 * @param SkippedManuscriptPrompt &$skippedManuscriptPrompt
	 * @return SkippedManuscriptPromptEqualsConstraint
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	): SkippedManuscriptPromptEqualsConstraint {
		return new SkippedManuscriptPromptEqualsConstraint( $skippedManuscriptPrompt );
	}

	/**
	 * @since 0.1.0
	 * @param User &$user
	 * @return UserEqualsConstraint
	 */
	public function visitUser(
		User &$user
	): UserEqualsConstraint {
		return new UserEqualsConstraint( $user );
	}

	/**
	 * @since 0.1.0
	 * @param UserDialect &$userDialect
	 * @return UserDialectEqualsConstraint
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	): UserDialectEqualsConstraint {
		return new UserDialectEqualsConstraint( $userDialect );
	}

	/**
	 * @since 0.1.0
	 * @param UserLanguageProficiencyLevel &$languageProficiencyLevel
	 * @return UserLanguageProficiencyLevelEqualsConstraint
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	): UserLanguageProficiencyLevelEqualsConstraint {
		return new UserLanguageProficiencyLevelEqualsConstraint( $languageProficiencyLevel );
	}

}

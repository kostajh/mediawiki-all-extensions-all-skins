<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

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
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Class CRUDFactory
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 *
 * A {@link PersistentVisitor}
 * that return the corresponding {@link CRUD}
 * that represents the given {@link Persistent}.
 *
 * @since 0.1.0
 */
class CRUDFactory implements PersistentVisitor {

	/** @var ILoadBalancer */
	private $dbLoadBalancer;

	/**
	 * CRUDFactory constructor.
	 * @param ILoadBalancer $dbLoadBalancer
	 */
	public function __construct(
		ILoadBalancer $dbLoadBalancer
	) {
		$this->dbLoadBalancer = $dbLoadBalancer;
	}

	/**
	 * @param Language &$language
	 * @return LanguageCRUD
	 */
	public function visitLanguage(
		Language &$language
	): LanguageCRUD {
		return new LanguageCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param Manuscript &$manuscript
	 * @return ManuscriptCRUD
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	): ManuscriptCRUD {
		return new ManuscriptCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param ManuscriptDomain &$manuscriptDomain
	 * @return ManuscriptDomainCRUD
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	): ManuscriptDomainCRUD {
		return new ManuscriptDomainCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param ManuscriptPrompt &$manuscriptPrompt
	 * @return ManuscriptPromptCRUD
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	): ManuscriptPromptCRUD {
		return new ManuscriptPromptCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param Recording &$recording
	 * @return RecordingCRUD
	 */
	public function visitRecording(
		Recording &$recording
	): RecordingCRUD {
		return new RecordingCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param RecordingAnnotation &$recordingAnnotation
	 * @return RecordingAnnotationCRUD
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	): RecordingAnnotationCRUD {
		return new RecordingAnnotationCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param RecordingAnnotationStereotype &$recordingAnnotationStereotype
	 * @return RecordingAnnotationStereotypeCRUD
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	): RecordingAnnotationStereotypeCRUD {
		return new RecordingAnnotationStereotypeCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param RecordingReview &$recordingReview
	 * @return RecordingReviewCRUD
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	): RecordingReviewCRUD {
		return new RecordingReviewCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param SkippedManuscriptPrompt &$skippedManuscriptPrompt
	 * @return SkippedManuscriptPromptCRUD
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	): SkippedManuscriptPromptCRUD {
		return new SkippedManuscriptPromptCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param User &$user
	 * @return UserCRUD
	 */
	public function visitUser(
		User &$user
	): UserCRUD {
		return new UserCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param UserDialect &$userDialect
	 * @return UserDialectCRUD
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	): UserDialectCRUD {
		return new UserDialectCRUD( $this->dbLoadBalancer );
	}

	/**
	 * @param UserLanguageProficiencyLevel &$languageProficiencyLevel
	 * @return UserLanguageProficiencyLevelCRUD
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	): UserLanguageProficiencyLevelCRUD {
		return new UserLanguageProficiencyLevelCRUD( $this->dbLoadBalancer );
	}

}

<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Language;
use MediaWiki\WikispeechSpeechDataCollector\Domain\LanguageProficiencyLevel;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript;
use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptDomain;
use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptPrompt;
use MediaWiki\WikispeechSpeechDataCollector\Domain\PersistentVisitor;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Recording;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotationStereotype;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingReview;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingReviewValue;
use MediaWiki\WikispeechSpeechDataCollector\Domain\SkippedManuscriptPrompt;
use MediaWiki\WikispeechSpeechDataCollector\Domain\User;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserDialect;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserLanguageProficiencyLevel;
use MediaWiki\WikispeechSpeechDataCollector\UUID;
use MWTimestamp;

/**
 * Sets at least all required values to non-null values in the visited instance,
 * except for the identity.
 * The combination of values set by this visitor
 * and {@link PersistentCompleteTwoBuilder}
 * must set all required and non required values,
 * and the non-null values the two visitors set must differ from each other.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class PersistentCompleteOneBuilder implements PersistentVisitor {

	/**
	 * @param Language &$language
	 * @return null
	 */
	public function visitLanguage(
		Language &$language
	) {
		$language->setNativeName( 'Svenska' );
		$language->setIso639a1( 'sv' );
		$language->setIso639a2b( 's2b' );
		$language->setIso639a2t( 's2t' );
		$language->setIso639a3( 's3' );
		return null;
	}

	/**
	 * @param Manuscript &$manuscript
	 * @return null
	 */
	public function visitManuscript(
		Manuscript &$manuscript
	) {
		$manuscript->setLanguage( UUID::v4BytesFactory() );
		$manuscript->setDomain( UUID::v4BytesFactory() );
		$manuscript->setDisabled( MWTimestamp::getInstance( 20200712185000 ) );
		$manuscript->setName( 'Manuscript name' );
		$manuscript->setCreated( MWTimestamp::getInstance( 20200711145000 ) );
		return null;
	}

	/**
	 * @param ManuscriptDomain &$manuscriptDomain
	 * @return null
	 */
	public function visitManuscriptDomain(
		ManuscriptDomain &$manuscriptDomain
	) {
		$manuscriptDomain->setName( 'Name' );
		$manuscriptDomain->setParent( UUID::v4BytesFactory() );
		return null;
	}

	/**
	 * @param ManuscriptPrompt &$manuscriptPrompt
	 * @return null
	 */
	public function visitManuscriptPrompt(
		ManuscriptPrompt &$manuscriptPrompt
	) {
		$manuscriptPrompt->setManuscript( UUID::v4BytesFactory() );
		$manuscriptPrompt->setIndex( 1 );
		$manuscriptPrompt->setContent( 'Content' );
		return null;
	}

	/**
	 * @param Recording &$recording
	 * @return null
	 */
	public function visitRecording(
		Recording &$recording
	) {
		$recording->setManuscriptPrompt( UUID::v4BytesFactory() );
		$recording->setSpokenDialect( UUID::v4BytesFactory() );
		$recording->setVoiceOf( UUID::v4BytesFactory() );
		$recording->setRecorded( MWTimestamp::getInstance( 20200713145000 ) );
		return null;
	}

	/**
	 * @param RecordingAnnotation &$recordingAnnotation
	 * @return null
	 */
	public function visitRecordingAnnotation(
		RecordingAnnotation &$recordingAnnotation
	) {
		$recordingAnnotation->setRecording( UUID::v4BytesFactory() );
		$recordingAnnotation->setStart( 1 );
		$recordingAnnotation->setEnd( 2 );
		$recordingAnnotation->setStereotype( UUID::v4BytesFactory() );
		$recordingAnnotation->setValue( 'Value' );
		return null;
	}

	/**
	 * @param RecordingAnnotationStereotype &$recordingAnnotationStereotype
	 * @return null
	 */
	public function visitRecordingAnnotationStereotype(
		RecordingAnnotationStereotype &$recordingAnnotationStereotype
	) {
		$recordingAnnotationStereotype->setDescription( 'A string' );
		$recordingAnnotationStereotype->setValueClass( 'string' );
		return null;
	}

	/**
	 * @param RecordingReview &$recordingReview
	 * @return null
	 */
	public function visitRecordingReview(
		RecordingReview &$recordingReview
	) {
		$recordingReview->setCreated( MWTimestamp::getInstance( 20200713145000 ) );
		$recordingReview->setReviewer( UUID::v4BytesFactory() );
		$recordingReview->setRecording( UUID::v4BytesFactory() );
		$recordingReview->setValue( RecordingReviewValue::THUMB_UP );
		return null;
	}

	/**
	 * @param SkippedManuscriptPrompt &$skippedManuscriptPrompt
	 * @return null
	 */
	public function visitSkippedManuscriptPrompt(
		SkippedManuscriptPrompt &$skippedManuscriptPrompt
	) {
		$skippedManuscriptPrompt->setSkipped( MWTimestamp::getInstance( 20200713145000 ) );
		$skippedManuscriptPrompt->setUser( UUID::v4BytesFactory() );
		$skippedManuscriptPrompt->setManuscriptPrompt( UUID::v4BytesFactory() );
		return null;
	}

	/**
	 * @param User &$user
	 * @return null
	 */
	public function visitUser(
		User &$user
	) {
		$user->setYearBorn( 1911 );
		$user->setMediaWikiUser( 123 );
		return null;
	}

	/**
	 * @param UserDialect &$userDialect
	 * @return null
	 */
	public function visitUserDialect(
		UserDialect &$userDialect
	) {
		$userDialect->setUser( UUID::v4BytesFactory() );
		$userDialect->setLanguage( UUID::v4BytesFactory() );
		$userDialect->setSpokenProficiencyLevel( LanguageProficiencyLevel::NATIVE );
		$userDialect->setLocation(
			'{ "type": "Feature", "geometry": { "type": "Point", "coordinates": [55.5, 13] } }'
		);
		return null;
	}

	/**
	 * @param UserLanguageProficiencyLevel &$languageProficiencyLevel
	 * @return null
	 */
	public function visitUserLanguageProficiencyLevel(
		UserLanguageProficiencyLevel &$languageProficiencyLevel
	) {
		$languageProficiencyLevel->setUser( UUID::v4BytesFactory() );
		$languageProficiencyLevel->setLanguage( UUID::v4BytesFactory() );
		$languageProficiencyLevel->setProficiencyLevel( LanguageProficiencyLevel::ADVANCED );
		return null;
	}
}

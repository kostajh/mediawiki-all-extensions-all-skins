<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

use MWTimestamp;

/**
 * Class Recording
 *
 * The audio representation of a {@link ManuscriptPrompt}
 * recorded by a given {@link User} in a given {@link UserDialect}.
 *
 * @todo <p> We could (should?) add indices in this object to avoid expensive
 * joining of tables and database end calculations:
 * * Age of user at recording (Recording->recorded - Recording->voiceOf->yearBorn)
 * * Recorded language (Recording->manuscriptPrompt->manuscript->language)
 * * etc
 * I.e. use denormalized information in the database, not database indices.
 * </p>
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class Recording implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var MWTimestamp|null */
	private $recorded;

	/**
	 * @var string|null 128 bits UUID
	 * @see User::$identity
	 */
	private $voiceOf;

	/**
	 * @var string|null 128 bits UUID
	 * @see UserDialect::$identity
	 */
	private $spokenDialect;

	/**
	 * @var string|null 128 bits UUID
	 * @see ManuscriptPrompt::$identity
	 */
	private $manuscriptPrompt;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitRecording( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'recorded => "' . $this->getRecorded() . '", ' .
			'voiceOf => "' . $this->getVoiceOf() . '", ' .
			'spokenDialect => "' . $this->getSpokenDialect() . '", ' .
			'manuscriptPrompt => "' . $this->getManuscriptPrompt() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see Recording::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see Recording::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see Recording::$recorded
	 * @return MWTimestamp|null
	 */
	public function getRecorded(): ?MWTimestamp {
		return $this->recorded;
	}

	/**
	 * @see Recording::$recorded
	 * @param MWTimestamp|null $recorded
	 */
	public function setRecorded( ?MWTimestamp $recorded ): void {
		$this->recorded = $recorded;
	}

	/**
	 * @see Recording::$voiceOf
	 * @return string|null
	 */
	public function getVoiceOf(): ?string {
		return $this->voiceOf;
	}

	/**
	 * @see Recording::$voiceOf
	 * @param string|null $voiceOf
	 */
	public function setVoiceOf( ?string $voiceOf ): void {
		$this->voiceOf = $voiceOf;
	}

	/**
	 * @see Recording::$spokenDialect
	 * @return string|null
	 */
	public function getSpokenDialect(): ?string {
		return $this->spokenDialect;
	}

	/**
	 * @see Recording::$spokenDialect
	 * @param string|null $spokenDialect
	 */
	public function setSpokenDialect( ?string $spokenDialect ): void {
		$this->spokenDialect = $spokenDialect;
	}

	/**
	 * @see Recording::$manuscriptPrompt
	 * @return string|null
	 */
	public function getManuscriptPrompt(): ?string {
		return $this->manuscriptPrompt;
	}

	/**
	 * @see Recording::$manuscriptPrompt
	 * @param string|null $manuscriptPrompt
	 */
	public function setManuscriptPrompt( ?string $manuscriptPrompt ): void {
		$this->manuscriptPrompt = $manuscriptPrompt;
	}
}

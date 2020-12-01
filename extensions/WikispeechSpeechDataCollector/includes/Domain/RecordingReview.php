<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

use MWTimestamp;

/**
 * Class RecordingReview
 *
 * Whether or not a {@link Recording} is good according to a specific {@link User}.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class RecordingReview implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var MWTimestamp|null */
	private $created;

	/**
	 * @var int|null
	 * @see RecordingReviewValue
	 */
	private $value;

	/**
	 * @var string|null 128 bits UUID
	 * @see User::$identity
	 */
	private $reviewer;

	/**
	 * @var string|null 128 bits UUID
	 * @see Recording::$identity
	 */
	private $recording;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitRecordingReview( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'created => "' . $this->getCreated() . '", ' .
			'value => "' . $this->getValue() . '", ' .
			'reviewer => "' . $this->getReviewer() . '", ' .
			'recording => "' . $this->getRecording() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see RecordingReview::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see RecordingReview::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see RecordingReview::$created
	 * @return MWTimestamp|null
	 */
	public function getCreated(): ?MWTimestamp {
		return $this->created;
	}

	/**
	 * @see RecordingReview::$created
	 * @param MWTimestamp|null $created
	 */
	public function setCreated( ?MWTimestamp $created ): void {
		$this->created = $created;
	}

	/**
	 * @see RecordingReview::$value
	 * @return int|null
	 */
	public function getValue(): ?int {
		return $this->value;
	}

	/**
	 * @see RecordingReview::$value
	 * @param int|null $value
	 */
	public function setValue( ?int $value ): void {
		$this->value = $value;
	}

	/**
	 * @see RecordingReview::$reviewer
	 * @return string|null
	 */
	public function getReviewer(): ?string {
		return $this->reviewer;
	}

	/**
	 * @see RecordingReview::$reviewer
	 * @param string|null $reviewer
	 */
	public function setReviewer( ?string $reviewer ): void {
		$this->reviewer = $reviewer;
	}

	/**
	 * @see RecordingReview::$recording
	 * @return string|null
	 */
	public function getRecording(): ?string {
		return $this->recording;
	}

	/**
	 * @see RecordingReview::$recording
	 * @param string|null $recording
	 */
	public function setRecording( ?string $recording ): void {
		$this->recording = $recording;
	}
}

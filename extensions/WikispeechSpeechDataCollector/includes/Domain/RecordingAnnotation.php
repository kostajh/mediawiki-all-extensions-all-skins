<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class RecordingAnnotation
 *
 * Metadata describing a part between a start and an endpoint
 * of the audio in a {@link Recording}.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class RecordingAnnotation implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
	 * @var string|null 128 bits UUID
	 * @see Recording::$identity
	 */
	private $recording;

	/** @var int|null Start position in milliseconds of recorded audio. */
	private $start;

	/** @var int|null End posision in milliseconds of recorded audio. */
	private $end;

	/**
	 * @var string|null 128 bits UUID
	 * @see RecordingAnnotationStereotype::$identity
	 */
	private $stereotype;

	/**
	 * Weak dynamically typed.
	 * Currently stored as a string. It's up to the user to cast it to the right type.
	 * @todo In the future, join in the value class and cast it on deserialization.
	 * @see RecordingAnnotationStereotype::$valueClass
	 * @var string|null
	 */
	private $value;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitRecordingAnnotation( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'recording => "' . $this->getRecording() . '", ' .
			'start => "' . $this->getStart() . '", ' .
			'end => "' . $this->getEnd() . '", ' .
			'stereotype => "' . $this->getStereotype() . '", ' .
			'value => "' . $this->getValue() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see RecordingAnnotation::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see RecordingAnnotation::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see RecordingAnnotation::$recording
	 * @return string|null
	 */
	public function getRecording(): ?string {
		return $this->recording;
	}

	/**
	 * @see RecordingAnnotation::$recording
	 * @param string|null $recording
	 */
	public function setRecording( ?string $recording ): void {
		$this->recording = $recording;
	}

	/**
	 * @see RecordingAnnotation::$start
	 * @return int|null
	 */
	public function getStart(): ?int {
		return $this->start;
	}

	/**
	 * @see RecordingAnnotation::$start
	 * @param int|null $start
	 */
	public function setStart( ?int $start ): void {
		$this->start = $start;
	}

	/**
	 * @see RecordingAnnotation::$end
	 * @return int|null
	 */
	public function getEnd(): ?int {
		return $this->end;
	}

	/**
	 * @see RecordingAnnotation::$end
	 * @param int|null $end
	 */
	public function setEnd( ?int $end ): void {
		$this->end = $end;
	}

	/**
	 * @see RecordingAnnotation::$stereotype
	 * @return string|null
	 */
	public function getStereotype(): ?string {
		return $this->stereotype;
	}

	/**
	 * @see RecordingAnnotation::$stereotype
	 * @param string|null $stereotype
	 */
	public function setStereotype( ?string $stereotype ): void {
		$this->stereotype = $stereotype;
	}

	/**
	 * @see RecordingAnnotation::$value
	 * @return string|null
	 */
	public function getValue(): ?string {
		return $this->value;
	}

	/**
	 * @see RecordingAnnotation::$value
	 * @param string|null $value
	 */
	public function setValue( ?string $value ): void {
		$this->value = $value;
	}
}

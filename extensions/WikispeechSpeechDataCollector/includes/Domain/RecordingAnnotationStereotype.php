<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class RecordingAnnotationStereotype
 *
 * Information in both machine- and human readable form
 * on how to interpret the value of a {@link RecordingAnnotation}.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class RecordingAnnotationStereotype implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var string|null type of value stored in {@link RecordingAnnotation::$value} */
	private $valueClass;

	/** @var string|null Explanation of how this annotation stereotype is used. */
	private $description;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitRecordingAnnotationStereotype( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'valueClass => "' . $this->getValueClass() . '", ' .
			'description => "' . $this->getDescription() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see RecordingAnnotationStereotype::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see RecordingAnnotationStereotype::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see RecordingAnnotationStereotype::$valueClass
	 * @return string|null
	 */
	public function getValueClass(): ?string {
		return $this->valueClass;
	}

	/**
	 * @see RecordingAnnotationStereotype::$valueClass
	 * @param string|null $valueClass
	 */
	public function setValueClass( ?string $valueClass ): void {
		$this->valueClass = $valueClass;
	}

	/**
	 * @see RecordingAnnotationStereotype::$description
	 * @return string|null
	 */
	public function getDescription(): ?string {
		return $this->description;
	}

	/**
	 * @see RecordingAnnotationStereotype::$description
	 * @param string|null $description
	 */
	public function setDescription( ?string $description ): void {
		$this->description = $description;
	}
}

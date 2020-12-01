<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class ManuscriptPrompt
 * Immutable (once there is at least one recording pointing at the instance).
 *
 * An ordered part of text in a {@link Manuscript}. A {@link Recording} is
 * the audio representation of a manuscript prompt.
 *
 * @todo <p>Consider adding information about how difficult the content of
 * the prompt is to read, and try feeding new users with as simple text as
 * possible to not scare them away from this system.</p>
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class ManuscriptPrompt implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
	 * @var string|null 128 bits UUID
	 * @see Manuscript::$identity
	 */
	private $manuscript;

	/** @var int|null Index order of this text in complete manuscript. */
	private $index;

	/** @var string|null Text content */
	private $content;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitManuscriptPrompt( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'manuscript => "' . $this->getManuscript() . '", ' .
			'index => "' . $this->getIndex() . '", ' .
			'content => "' . $this->getContent() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see ManuscriptPrompt::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see ManuscriptPrompt::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see ManuscriptPrompt::$manuscript
	 * @return string|null
	 */
	public function getManuscript(): ?string {
		return $this->manuscript;
	}

	/**
	 * @see ManuscriptPrompt::$manuscript
	 * @param string|null $manuscript
	 */
	public function setManuscript( ?string $manuscript ): void {
		$this->manuscript = $manuscript;
	}

	/**
	 * @see ManuscriptPrompt::$index
	 * @return int|null
	 */
	public function getIndex(): ?int {
		return $this->index;
	}

	/**
	 * @see ManuscriptPrompt::$index
	 * @param int|null $index
	 */
	public function setIndex( ?int $index ): void {
		$this->index = $index;
	}

	/**
	 * @see ManuscriptPrompt::$content
	 * @return string|null
	 */
	public function getContent(): ?string {
		return $this->content;
	}

	/**
	 * @see ManuscriptPrompt::$content
	 * @param string|null $content
	 */
	public function setContent( ?string $content ): void {
		$this->content = $content;
	}
}

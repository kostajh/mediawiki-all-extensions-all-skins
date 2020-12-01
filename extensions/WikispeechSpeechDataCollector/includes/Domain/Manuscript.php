<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

use MWTimestamp;

/**
 * Class Manuscript
 *
 * A manuscript is a complete text which is required to be completely recorded
 * in order to produce a voice synthesizer. Manuscripts are broken down in
 * smaller parts, {@link ManuscriptPrompt}, ordered segments with a lexical flow,
 * large enough for a single recording.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class Manuscript implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var string|null */
	private $name;

	/** @var MWTimestamp|null */
	private $created;

	/** @var MWTimestamp|null */
	private $disabled;

	/**
	 * @var string|null 128 bits UUID
	 * @see Language::$identity
	 */
	private $language;

	/**
	 * @var string|null 128 bits UUID
	 * @see ManuscriptDomain::$identity
	 */
	private $domain;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitManuscript( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'name => "' . $this->getName() . '", ' .
			'created => "' . $this->getCreated() . '", ' .
			'disabled => "' . $this->getDisabled() . '", ' .
			'language => "' . $this->getLanguage() . '", ' .
			'domain => "' . $this->getDomain() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see Manuscript::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see Manuscript::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see Manuscript::$name
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
	}

	/**
	 * @see Manuscript::$name
	 * @param string|null $name
	 */
	public function setName( ?string $name ): void {
		$this->name = $name;
	}

	/**
	 * @see Manuscript::$created
	 * @return MWTimestamp|null
	 */
	public function getCreated(): ?MWTimestamp {
		return $this->created;
	}

	/**
	 * @see Manuscript::$created
	 * @param MWTimestamp|null $created
	 */
	public function setCreated( ?MWTimestamp $created ): void {
		$this->created = $created;
	}

	/**
	 * @see Manuscript::$disabled
	 * @return MWTimestamp|null
	 */
	public function getDisabled(): ?MWTimestamp {
		return $this->disabled;
	}

	/**
	 * @see Manuscript::$disabled
	 * @param MWTimestamp|null $disabled
	 */
	public function setDisabled( ?MWTimestamp $disabled ): void {
		$this->disabled = $disabled;
	}

	/**
	 * @see Manuscript::$language
	 * @return string|null
	 */
	public function getLanguage(): ?string {
		return $this->language;
	}

	/**
	 * @see Manuscript::$language
	 * @param string|null $language
	 */
	public function setLanguage( ?string $language ): void {
		$this->language = $language;
	}

	/**
	 * @see Manuscript::$domain
	 * @return string|null
	 */
	public function getDomain(): ?string {
		return $this->domain;
	}

	/**
	 * @see Manuscript::$domain
	 * @param string|null $domain
	 */
	public function setDomain( ?string $domain ): void {
		$this->domain = $domain;
	}
}

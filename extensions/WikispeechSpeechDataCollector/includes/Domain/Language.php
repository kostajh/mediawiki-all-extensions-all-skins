<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class Language
 *
 * All languages in the world does not exist in all ISO639-codes. Thus in order
 * to really keep track of them we need to keep track of all ISO639-codes.
 * However, ISO639-codes are meant to be compositely unique. So for instance a
 * code available in 639-2t should never be another language in 639-3. It might
 * thus be enough to keep a list of 639-codes per language rather than keeping
 * track of specific codes per 639-grouping.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class Language implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var string|null Name of language in that language, e.g. 'English'. */
	private $nativeName;

	/** @var string|null https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes */
	private $iso639a1;

	/** @var string|null https://en.wikipedia.org/wiki/ISO_639-2 */
	private $iso639a2b;

	/** @var string|null https://en.wikipedia.org/wiki/ISO_639-2 */
	private $iso639a2t;

	/** @var string|null https://en.wikipedia.org/wiki/ISO_639-3 */
	private $iso639a3;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitLanguage( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'nativeName => "' . $this->getNativeName() . '", ' .
			'iso639a1 => "' . $this->getIso639a1() . '", ' .
			'iso639a2b => "' . $this->getIso639a2b() . '", ' .
			'iso639a2t => "' . $this->getIso639a2t() . '", ' .
			'iso639a3 => "' . $this->getIso639a3() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see Language::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see Language::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see Language::$nativeName
	 * @return string|null
	 */
	public function getNativeName(): ?string {
		return $this->nativeName;
	}

	/**
	 * @see Language::$nativeName
	 * @param string|null $nativeName
	 */
	public function setNativeName( ?string $nativeName ): void {
		$this->nativeName = $nativeName;
	}

	/**
	 * @see Language::$iso639a1
	 * @return string|null
	 */
	public function getIso639a1(): ?string {
		return $this->iso639a1;
	}

	/**
	 * @see Language::$iso639a1
	 * @param string|null $iso639a1
	 */
	public function setIso639a1( ?string $iso639a1 ): void {
		$this->iso639a1 = $iso639a1;
	}

	/**
	 * @see Language::$iso639a2b
	 * @return string|null
	 */
	public function getIso639a2b(): ?string {
		return $this->iso639a2b;
	}

	/**
	 * @see Language::$iso639a2b
	 * @param string|null $iso639a2b
	 */
	public function setIso639a2b( ?string $iso639a2b ): void {
		$this->iso639a2b = $iso639a2b;
	}

	/**
	 * @see Language::$iso639a2t
	 * @return string|null
	 */
	public function getIso639a2t(): ?string {
		return $this->iso639a2t;
	}

	/**
	 * @see Language::$iso639a2t
	 * @param string|null $iso639a2t
	 */
	public function setIso639a2t( ?string $iso639a2t ): void {
		$this->iso639a2t = $iso639a2t;
	}

	/**
	 * @see Language::$iso639a3
	 * @return string|null
	 */
	public function getIso639a3(): ?string {
		return $this->iso639a3;
	}

	/**
	 * @see Language::$iso639a3
	 * @param string|null $iso639a3
	 */
	public function setIso639a3( ?string $iso639a3 ): void {
		$this->iso639a3 = $iso639a3;
	}
}

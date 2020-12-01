<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class UserDialect
 *
 * How well a {@link User} speaks a specific dialect of a {@link Language}
 * and the geographic area that best describes the accent or dialect.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class UserDialect implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
	 * A user can be associated with multiple dialects at different levels.
	 *
	 * @var string|null 128 bits UUID
	 * @see User::$identity
	 */
	private $user;

	/**
	 * @var string|null 128 bits UUID
	 * @see Language::$identity
	 */
	private $language;

	/**
	 * @var int|null How well the user speaks this language
	 * @see LanguageProficiencyLevel
	 */
	private $spokenProficiencyLevel;

	/**
	 * Geometry that best fit the area that represents the spoken dialect.
	 *
	 * A German speaking Swedish with a broken accent
	 * might best be represented by an envelope over a large part of Germany.
	 *
	 * A native Swedish person living in Stockholm for the last 40 years but still
	 * speak with a Gothenburg dialect might best be described with an envelope
	 * covering a large area that also include Gothenburg.
	 *
	 * A person living in an suburb that is known to have a dialect that differs
	 * from the rest of the surrounding area, perhaps due to socioeconomic factors
	 * towards one way or the other, it might be best to pinpoint that area rather
	 * than defining a geometry that spans the greater city area.
	 *
	 * @todo <p>How do we defined geometries here? GeoJson lib?
	 * How do we enable GIS queries in the database?</p>
	 * @var string|null
	 */
	private $location;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitUserDialect( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'user => "' . $this->getUser() . '", ' .
			'language => "' . $this->getLanguage() . '", ' .
			'spokenProficiencyLevel => "' . $this->getSpokenProficiencyLevel() . '", ' .
			'location => "' . $this->getLocation() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see UserDialect::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see UserDialect::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see UserDialect::$user
	 * @return string|null
	 */
	public function getUser(): ?string {
		return $this->user;
	}

	/**
	 * @see UserDialect::$user
	 * @param string|null $user
	 */
	public function setUser( ?string $user ): void {
		$this->user = $user;
	}

	/**
	 * @see UserDialect::$language
	 * @return string|null
	 */
	public function getLanguage(): ?string {
		return $this->language;
	}

	/**
	 * @see UserDialect::$language
	 * @param string|null $language
	 */
	public function setLanguage( ?string $language ): void {
		$this->language = $language;
	}

	/**
	 * @see UserDialect::$spokenProficiencyLevel
	 * @return int|null
	 */
	public function getSpokenProficiencyLevel(): ?int {
		return $this->spokenProficiencyLevel;
	}

	/**
	 * @see UserDialect::$spokenProficiencyLevel
	 * @param int|null $spokenProficiencyLevel
	 */
	public function setSpokenProficiencyLevel( ?int $spokenProficiencyLevel ): void {
		$this->spokenProficiencyLevel = $spokenProficiencyLevel;
	}

	/**
	 * @see UserDialect::$location
	 * @return string|null
	 */
	public function getLocation(): ?string {
		return $this->location;
	}

	/**
	 * @see UserDialect::$location
	 * @param string|null $location
	 */
	public function setLocation( ?string $location ): void {
		$this->location = $location;
	}
}

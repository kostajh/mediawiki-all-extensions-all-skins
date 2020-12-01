<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class UserLanguageProficiencyLevel
 *
 * How well a {@link User} understands a given {@link Language}
 * in written or spoken form.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class UserLanguageProficiencyLevel implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
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
	 * @var int|null
	 * @see LanguageProficiencyLevel
	 */
	private $proficiencyLevel;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitUserLanguageProficiencyLevel( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'user => "' . $this->getUser() . '", ' .
			'language => "' . $this->getLanguage() . '", ' .
			'proficiencyLevel => "' . $this->getProficiencyLevel() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see UserLanguageProficiencyLevel::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$user
	 * @return string|null
	 */
	public function getUser(): ?string {
		return $this->user;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$user
	 * @param string|null $user
	 */
	public function setUser( ?string $user ): void {
		$this->user = $user;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$language
	 * @return string|null
	 */
	public function getLanguage(): ?string {
		return $this->language;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$language
	 * @param string|null $language
	 */
	public function setLanguage( ?string $language ): void {
		$this->language = $language;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$proficiencyLevel
	 * @return int|null
	 */
	public function getProficiencyLevel(): ?int {
		return $this->proficiencyLevel;
	}

	/**
	 * @see UserLanguageProficiencyLevel::$proficiencyLevel
	 * @param int|null $proficiencyLevel
	 */
	public function setProficiencyLevel( ?int $proficiencyLevel ): void {
		$this->proficiencyLevel = $proficiencyLevel;
	}
}

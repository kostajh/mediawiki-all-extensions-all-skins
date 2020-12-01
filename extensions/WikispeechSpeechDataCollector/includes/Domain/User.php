<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class User
 *
 * An augmentation of the MediaWiki {@link \User}. Allows for easily adding new
 * information that make sense in this extension but might not be accepted in
 * the MediaWiki core. Also, by keeping track of users with our own identity
 * we're not pointing to the MediaWiki user identity as relations from other
 * tables. This allows us to export data from this system and while keeping
 * users completely anonymous.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class User implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
	 * @var int|null {@link \User::getId()}
	 */
	private $mediaWikiUser;

	/**
	 * @var int|null Year born, Gregorian calendar.
	 * Used to evaluate age of user in a recorded voice.
	 * @todo Consider if we should store age of recoded user in each recording as an index.
	 */
	private $yearBorn;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitUser( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'wikimediaUser => "' . $this->getMediaWikiUser() . '", ' .
			'yearBorn => "' . $this->getYearBorn() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see User::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see User::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see User::$mediaWikiUser
	 * @return int|null
	 */
	public function getMediaWikiUser(): ?int {
		return $this->mediaWikiUser;
	}

	/**
	 * @see User::$mediaWikiUser
	 * @param int|null $mediaWikiUser
	 */
	public function setMediaWikiUser( ?int $mediaWikiUser ): void {
		$this->mediaWikiUser = $mediaWikiUser;
	}

	/**
	 * @see User::$yearBorn
	 * @return int|null
	 */
	public function getYearBorn(): ?int {
		return $this->yearBorn;
	}

	/**
	 * @see User::$yearBorn
	 * @param int|null $yearBorn
	 */
	public function setYearBorn( ?int $yearBorn ): void {
		$this->yearBorn = $yearBorn;
	}
}

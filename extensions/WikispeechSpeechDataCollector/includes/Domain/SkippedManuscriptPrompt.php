<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

use MWTimestamp;

/**
 * Class SkippedManuscriptPrompt
 *
 * Indication that a {@link User} skipped recording
 * a specific {@link ManuscriptPrompt}.
 *
 * @todo <p>Consider allowing a reason for skipping,
 * e.g. too complex pronunciation, foul language, etc.</p>
 *
 * In the future, this could easily be mined together in conjunction with
 * {@link UserLanguageProficiencyLevel::$proficiencyLevel}
 * in order to figure out what prompts to not expose to new users
 * and thus make onboarding as easy as possible rather than scare them
 * away with hard to read sentences.
 *
 * Tokenized {@link ManuscriptPrompt::$content} with above mentioned data
 * could also be used to guess hard to read sentences in prompts that has
 * never been exposed to users.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class SkippedManuscriptPrompt implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/**
	 * @var string|null 128 bits UUID
	 * @see Manuscript::$identity
	 */
	private $manuscriptPrompt;

	/**
	 * @var string|null 128 bits UUID
	 * @see User::$identity
	 */
	private $user;

	/** @var MWTimestamp|null Timestamp skipped */
	private $skipped;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitSkippedManuscriptPrompt( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'manuscriptPrompt => "' . $this->getManuscriptPrompt() . '", ' .
			'user => "' . $this->getUser() . '", ' .
			'skipped => "' . $this->getSkipped() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see SkippedManuscriptPrompt::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see SkippedManuscriptPrompt::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see SkippedManuscriptPrompt::$manuscriptPrompt
	 * @return string|null
	 */
	public function getManuscriptPrompt(): ?string {
		return $this->manuscriptPrompt;
	}

	/**
	 * @see SkippedManuscriptPrompt::$manuscriptPrompt
	 * @param string|null $manuscriptPrompt
	 */
	public function setManuscriptPrompt( ?string $manuscriptPrompt ): void {
		$this->manuscriptPrompt = $manuscriptPrompt;
	}

	/**
	 * @see SkippedManuscriptPrompt::$user
	 * @return string|null
	 */
	public function getUser(): ?string {
		return $this->user;
	}

	/**
	 * @see SkippedManuscriptPrompt::$user
	 * @param string|null $user
	 */
	public function setUser( ?string $user ): void {
		$this->user = $user;
	}

	/**
	 * @see SkippedManuscriptPrompt::$skipped
	 * @return MWTimestamp|null
	 */
	public function getSkipped(): ?MWTimestamp {
		return $this->skipped;
	}

	/**
	 * @see SkippedManuscriptPrompt::$skipped
	 * @param MWTimestamp|null $skipped
	 */
	public function setSkipped( ?MWTimestamp $skipped ): void {
		$this->skipped = $skipped;
	}
}

<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class ManuscriptDomain
 *
 * A hierarchical grouping feature for manuscripts.
 *
 * @todo Consider if it makes more sense to use tags or such to group manuscripts.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class ManuscriptDomain implements Persistent {
	/** @var string|null 128 bits UUID */
	private $identity;

	/** @var string|null */
	private $name;

	/**
	 * @var string|null 128 bits UUID
	 * @see ManuscriptDomain::$identity
	 */
	private $parent;

	// visitor

	/**
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor ) {
		return $visitor->visitManuscriptDomain( $this );
	}

	public function __toString(): string {
		return '[ ' .
			'identity => "' . $this->getIdentity() . '", ' .
			'name => "' . $this->getName() . '", ' .
			'parent => "' . $this->getParent() . '" ' .
			']';
	}

	// getters and setters

	/**
	 * @see ManuscriptDomain::$identity
	 * @return string|null
	 */
	public function getIdentity(): ?string {
		return $this->identity;
	}

	/**
	 * @see ManuscriptDomain::$identity
	 * @param string|null $identity
	 */
	public function setIdentity( $identity ): void {
		$this->identity = $identity;
	}

	/**
	 * @see ManuscriptDomain::$name
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
	}

	/**
	 * @see ManuscriptDomain::$name
	 * @param string|null $name
	 */
	public function setName( ?string $name ): void {
		$this->name = $name;
	}

	/**
	 * @see ManuscriptDomain::$parent
	 * @return string|null
	 */
	public function getParent(): ?string {
		return $this->parent;
	}

	/**
	 * @see ManuscriptDomain::$parent
	 * @param string|null $parent
	 */
	public function setParent( ?string $parent ): void {
		$this->parent = $parent;
	}
}

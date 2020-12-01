<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Interface Persistent
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 *
 * A persistent class represents a domain object
 * that can be stored and retrieved from a database.
 *
 * A persistent class is a pure data container.
 * Any per subclass business logic should be implemented
 * using the visitor pattern {@link PersistentVisitor}.
 * Database access uses a CRUD-pattern {@link AbstractCRUD}.
 */
interface Persistent {

	/** @return mixed object identity */
	public function getIdentity();

	/**
	 * @param mixed $identity object identity
	 */
	public function setIdentity( $identity ) : void;

	/**
	 * Visitor pattern accepting function.
	 * https://en.wikipedia.org/wiki/Visitor_pattern
	 *
	 * Used to separate business logic from this pure data container class.
	 *
	 * @param PersistentVisitor $visitor
	 * @return mixed|null
	 */
	public function accept( PersistentVisitor $visitor );

	public function __toString(): string;
}

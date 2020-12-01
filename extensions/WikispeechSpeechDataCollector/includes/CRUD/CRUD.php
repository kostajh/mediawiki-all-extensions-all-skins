<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;

/**
 * Interface CRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 *
 * Database access for
 * instances of the corresponding underlying subclass of {@link Persistent}.
 *
 * @note Consider implementing Phan generics for hard typing subclass of Persistent.
 * @since 0.1.0
 */
interface CRUD {
	/**
	 * Given a persistent domain object instance with at least identity set,
	 * creates representation in database to correspond to the data set in the domain object.
	 *
	 * @since 0.1.0
	 * @param Persistent &$instance Instance to be inserted to the persistent layer.
	 */
	public function create(
		Persistent &$instance
	): void;

	/**
	 * Given an identity,
	 * retrieves a persistent domain object from the database.
	 *
	 * @see load()
	 * @param mixed $identity
	 * @return Persistent|null
	 */
	public function read(
		$identity
	): ?object;

	/**
	 * Given a persistent domain object instance with at least identity set,
	 * updates the domain object to correspond data retrieved from the database.
	 *
	 * @see read()
	 * @param Persistent &$instance instance to be loaded. Identity must be set.
	 * @return bool true if found, false if not found.
	 */
	public function load(
		Persistent &$instance
	): bool;

	/**
	 * Given a persistent domain object instance with at least identity set,
	 * updates the database to correspond to the data set in the domain object.
	 *
	 * @param Persistent $instance
	 */
	public function update(
		Persistent $instance
	): void;

	/**
	 * Given an identity,
	 * removes the corresponding persistent domain object from the database.
	 *
	 * @param mixed $identity
	 */
	public function delete(
		$identity
	): void;
}

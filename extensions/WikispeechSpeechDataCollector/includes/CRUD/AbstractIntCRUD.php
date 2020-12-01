<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use ExternalStoreException;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;

/**
 * Class AbstractIntCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 *
 * Expects that the identity is created during insert to table.
 */
abstract class AbstractIntCRUD extends AbstractCRUD {

	/**
	 * Given a persistent domain object instance with at least identity set,
	 * creates representation in database to correspond to the data set in the domain object.
	 *
	 * @param Persistent &$instance
	 * @throws ExternalStoreException If instance identity is already set
	 */
	public function create(
		Persistent &$instance
	): void {
		if ( $instance->getIdentity() ) {
			throw new ExternalStoreException( 'Identity already set' );
		}
		$rows = [];
		$this->serializeFields( $instance, $rows );
		$dbw = $this->dbLoadBalancer->getConnectionRef( DB_MASTER );
		$dbw->insert( $this->getTable(), $rows );
		$instance->setIdentity( $dbw->insertId() );
	}

	/**
	 * @param Persistent &$instance
	 * @param array $row
	 */
	protected function deserializeRowIdentity(
		Persistent &$instance,
		$row
	): void {
		$instance->setIdentity( intval( $row[ $this->getIdentityColumn() ] ) );
	}

	/**
	 * @param int $identity
	 * @return Persistent|null
	 */
	public function read(
		$identity
	): ?object {
		return parent::read( $identity );
	}

	/**
	 * @param int $identity
	 */
	public function delete(
		$identity
	): void {
		parent::delete( $identity );
	}
}

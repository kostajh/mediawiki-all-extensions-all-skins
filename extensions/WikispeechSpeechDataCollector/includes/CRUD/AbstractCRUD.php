<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\UUID;
use MWTimestamp;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Class AbstractCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 *
 * Database mapping and access for
 * instances of the corresponding underlying subclass of {@link Persistent}.
 *
 * @since 0.1.0
 */
abstract class AbstractCRUD implements CRUD {

	/** @var string Prefix of all extension tables in database. */
	protected const TABLES_PREFIX = 'wikispeech_sdc_';

	/** @var string Prefix of all columns in database extension tables. */
	protected const COLUMNS_PREFIX = 'wssdc';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	abstract protected function getClassColumnsPrefix() : string;

	/** @var string Name of table column that holds the identity. */
	private $identityColumn;

	/** @var ILoadBalancer */
	protected $dbLoadBalancer;

	/**
	 * AbstractCRUD constructor.
	 * @param ILoadBalancer $dbLoadBalancer
	 */
	public function __construct(
		ILoadBalancer $dbLoadBalancer
	) {
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->identityColumn = $this->getClassColumnsPrefix() . 'identity';
	}

	/**
	 * Single column identity for this CRUD.
	 *
	 * If you want to use a multi column identity you'll probably have to extract
	 * this method and all uses of it to a strategy pattern or something.
	 *
	 * @return string
	 */
	public function getIdentityColumn(): string {
		return $this->identityColumn;
	}

	/**
	 * @return string Name of database table representing this class.
	 */
	abstract protected function getTable(): string;

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	abstract protected function getColumns(): array;

	/**
	 * @var string[] Columns in table required to deserialize an instance, identity included.
	 */
	private $allColumns;

	/**
	 * @return array|string[] Columns in table required to deserialize an instance, identity included
	 */
	protected function getAllColumns(): array {
		if ( !$this->allColumns ) {
			$allColumns = $this->getColumns();
			array_push( $allColumns, $this->getIdentityColumn() );
			$this->allColumns = $allColumns;
		}
		return $this->allColumns;
	}

	/**
	 * Creates a new instance
	 * of the corresponding underlying subclass of {@link Persistent}.
	 *
	 * @return Persistent
	 */
	abstract public function instanceFactory(): Persistent;

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
	): ?object {
		$instance = $this->instanceFactory();
		$instance->setIdentity( $identity );
		return $this->load( $instance ) ? $instance : null;
	}

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
	): bool {
		return $this->loadByConditions( [
			$this->getIdentityColumn() => $instance->getIdentity()
		], $instance );
	}

	/**
	 * Given a persistent domain object instance with at least identity set,
	 * updates the database to correspond to the data set in the domain object.
	 *
	 * @param Persistent $instance
	 */
	public function update(
		Persistent $instance
	): void {
		$set = [];
		$this->serializeFields( $instance, $set );
		$dbw = $this->dbLoadBalancer->getConnectionRef( DB_MASTER );
		$dbw->update( $this->getTable(), $set, [
			$this->getIdentityColumn() => $instance->getIdentity()
		] );
	}

	/**
	 * Given an identity,
	 * removes the corresponding persistent domain object from the database.
	 *
	 * @param mixed $identity
	 */
	public function delete(
		$identity
	): void {
		$dbw = $this->dbLoadBalancer->getConnectionRef( DB_MASTER );
		$dbw->delete( $this->getTable(), [
			$this->getIdentityColumn() => $identity
		] );
	}

	/**
	 * Picks up a single instance. Creates a new object instance.
	 *
	 * @param array $conditions See {@link \IDatabase::select} conditions.
	 * @return Persistent|null
	 */
	public function getByConditions(
		array $conditions
	): ?Persistent {
		$instance = $this->instanceFactory();
		return $this->loadByConditions( $conditions, $instance ) ? $instance : null;
	}

	/**
	 * Picks up a single instance. Loads to existing object instance.
	 *
	 * @param array $conditions See {@link \IDatabase::select} conditions.
	 * @param Persistent &$instance Instance to be populated with data.
	 * @return bool true if found, false if not found.
	 */
	public function loadByConditions(
		array $conditions,
		Persistent &$instance
	): bool {
		$dbr = $this->dbLoadBalancer->getConnectionRef( DB_REPLICA );
		$res = $dbr->select( $this->getTable(), $this->getAllColumns(), $conditions );
		try {
			if ( !$res ) {
				return false;
			}
			$row = $dbr->fetchObject( $res );
			if ( !$row ) {
				return false;
			}
			$rowArray = (array)$row;
			$this->deserializeRowIdentity( $instance, $rowArray );
			$this->deserializeRow( $instance, $rowArray );
			return true;
		} finally {
			$dbr->freeResult( $res );
		}
	}

	/**
	 * @param array $conditions
	 * @return Persistent[]|null
	 */
	public function listByConditions(
		array $conditions
	): ?array {
		$dbr = $this->dbLoadBalancer->getConnectionRef( DB_REPLICA );
		$res = $dbr->select( $this->getTable(), $this->getAllColumns(), $conditions );
		if ( !$res ) {
			return null;
		}
		$instances = [];
		foreach ( $res as $row ) {
			$rowArray = (array)$row;
			$instance = $this->instanceFactory();
			$this->deserializeRowIdentity( $instance, $rowArray );
			$this->deserializeRow( $instance, $rowArray );
			array_push( $instances, $instance );
		}
		return $instances;
	}

	/**
	 * Reads all class fields except the identity field from the row.
	 *
	 * @param mixed &$instance An instance of the corresponding underlying Persistent subclass.
	 * @param array $row
	 */
	abstract protected function deserializeRow(
		&$instance,
		array $row
	): void;

	/**
	 * @param Persistent &$instance
	 * @param array $row
	 */
	abstract protected function deserializeRowIdentity(
		Persistent &$instance,
		$row
	): void;

	/**
	 * Adds all class fields except for the identity field
	 * to the array using the table column name as key.
	 *
	 * Used to execute create- and update statements.
	 *
	 * @param mixed $instance An instance of the corresponding underlying Persistent subclass.
	 * @param array &$array
	 */
	abstract protected function serializeFields(
		$instance,
		array &$array
	): void;

	/**
	 * @param array $row
	 * @param string $columnName
	 * @return string|null
	 */
	protected function deserializeString( array $row, string $columnName ): ?string {
		if ( !array_key_exists( $columnName, $row ) ) {
			return null;
		}
		return $row[$columnName] !== null ? strval( $row[$columnName] ) : null;
	}

	/**
	 * @param array $row
	 * @param string $columnName
	 * @return int|null
	 */
	protected function deserializeInt( array $row, string $columnName ): ?int {
		if ( !array_key_exists( $columnName, $row ) ) {
			return null;
		}
		return $row[$columnName] !== null ? intval( $row[$columnName] ) : null;
	}

	/**
	 * @param array $row
	 * @param string $columnName
	 * @return string|null
	 */
	protected function deserializeUuid( array $row, string $columnName ): ?string {
		if ( !array_key_exists( $columnName, $row ) ) {
			return null;
		}
		return $row[$columnName] !== null ? UUID::asBytes( strval( $row[$columnName] ) ) : null;
	}

	/**
	 * @param array $row
	 * @param string $columnName
	 * @return MWTimestamp|null
	 */
	protected function deserializeTimestamp( array $row, string $columnName ): ?MWTimestamp {
		if ( !array_key_exists( $columnName, $row ) ) {
			return null;
		}
		return $row[$columnName] !== null ? new MWTimestamp( $row[$columnName] ) : null;
	}
}

<?php

namespace BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;

use BlueSpice\Data\IStore;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;
use BlueSpice\ExtendedStatistics\Entity\Collection;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use Config;

abstract class SnapshotDiffCollector extends StoreSourced {
	/**
	 *
	 * @var SnapshotFactory
	 */
	protected $snapshotFactory = null;

	/**
	 * @var Collection
	 */
	protected $lastCollection = null;

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 * @param IStore $store
	 * @param SnapshotFactory $snapshotFactory
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory, IStore $store, SnapshotFactory $snapshotFactory ) {
		parent::__construct( $type, $snapshot, $config, $factory, $store );
		$this->snapshotFactory = $snapshotFactory;
	}

	/**
	 * Class for EntityCollection
	 *
	 * @return string
	 */
	abstract protected function getCollectionClass();

	/**
	 *
	 * @return Collection
	 */
	protected function getLastCollection() {
		if ( $this->lastCollection !== null ) {
			return $this->lastCollection;
		}
		$this->lastCollection = [];
		$snapshot = $this->snapshotFactory->getPrevious( $this->snapshot );
		if ( !$snapshot ) {
			return $this->lastCollection;
		}
		$expectedClass = $this->getCollectionClass();
		$this->lastCollection = array_filter(
			$snapshot->get( Snapshot::ATTR_COLLECTION ),
			function ( Collection $e ) use ( $expectedClass ) {
				return $e instanceof $expectedClass;
			}
		);
		return $this->lastCollection;
	}
}

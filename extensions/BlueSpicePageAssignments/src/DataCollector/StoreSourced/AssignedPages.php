<?php

namespace BlueSpice\PageAssignments\DataCollector\StoreSourced;

use BlueSpice\Data\IRecord;
use BlueSpice\Data\IStore;
use BlueSpice\Data\RecordSet;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\PageAssignments\Data\DataCollector\AssignedPages\Record as CollectorRecord;
use BlueSpice\PageAssignments\Data\Page\Record;
use BlueSpice\PageAssignments\Data\Page\Store;
use BlueSpice\PageAssignments\Entity\Collection\AssignedPages as Collection;
use Config;
use MediaWiki\MediaWikiServices;
use MWException;

class AssignedPages extends StoreSourced\NamespaceCollector {

	/**
	 *
	 * @var SnapshotFactory
	 */
	protected $snapshotFactory = null;

	/**
	 *
	 * @var array
	 */
	protected $namespaces = null;

	/**
	 *
	 * @var Collection[]
	 */
	protected $lastCollection = null;

	/**
	 * @param string $type
	 * @param MediaWikiServices $services
	 * @param Snapshot $snapshot
	 * @param Config|null $config
	 * @param EntityFactory|null $factory
	 * @param IStore|null $store
	 * @param SnapshotFactory|null $snapshotFactory
	 * @param array|null $namespaces
	 * @return static
	 * @throws MWException
	 */
	public static function factory( $type, MediaWikiServices $services, Snapshot $snapshot,
		Config $config = null, EntityFactory $factory = null, IStore $store = null,
		SnapshotFactory $snapshotFactory = null, array $namespaces = null ) {
		if ( !$config ) {
			$config = $snapshot->getConfig();
		}
		if ( !$factory ) {
			$factory = $services->getService( 'BSEntityFactory' );
		}
		if ( !$store ) {
			$context = \RequestContext::getMain();
			$context->setUser(
				$services->getService( 'BSUtilityFactory' )->getMaintenanceUser()->getUser()
			);
			$store = new Store( $context, $services->getDBLoadBalancer() );
		}
		if ( !$snapshotFactory ) {
			$snapshotFactory = $services->getService(
				'BSExtendedStatisticsSnapshotFactory'
			);
		}
		if ( !$namespaces ) {
			$namespaces = StoreSourced\NamespaceCollector::getNamespaces( $snapshot, $services );
		}

		return new static(
			$type,
			$snapshot,
			$config,
			$factory,
			$store,
			$snapshotFactory,
			$namespaces
		);
	}

	/**
	 *
	 * @return RecordSet
	 */
	protected function doCollect() {
		$res = parent::doCollect();
		$data = [];
		$assigned = $unassigned = $assignedAgg = $unassignedAgg = [];
		foreach ( $this->namespaces as $idx => $canonicalName ) {
			$assigned[$canonicalName] = $unassigned[$canonicalName]
			= $assignedAgg[$canonicalName] = $unassignedAgg[$canonicalName] = 0;
		}
		foreach ( $res->getRecords() as $record ) {
			if ( !isset( $this->namespaces[(int)$record->get( Record::NS )] ) ) {
				// removed or broken ns
				continue;
			}
			$nsName = $this->namespaces[(int)$record->get( Record::NS )];
			if ( !isset( $assignedAgg[$nsName] ) ) {
				$assignedAgg[$nsName] = 0;
			}
			if ( !isset( $unassignedAgg[$nsName] ) ) {
				$unassignedAgg[$nsName] = 0;
			}
			if ( !isset( $unassigned[$nsName] ) ) {
				$unassigned[$nsName] = 0;
			}
			if ( !isset( $assigned[$nsName] ) ) {
				$assigned[$nsName] = 0;
			}
			if ( empty( $record->get( Record::ASSIGNMENTS ) ) ) {
				$unassigned[$nsName] ++;
				$unassignedAgg[$nsName] ++;
			} else {
				$assigned[$nsName]++;
				$assignedAgg[$nsName] ++;
			}
		}

		$lastCollection = $this->getLastCollection();
		foreach ( $lastCollection as $collection ) {
			$nsName = $collection->get( Collection::ATTR_NAMESPACE_NAME );
			if ( !isset( $assignedAgg[$canonicalName] ) ) {
				$assignedAgg[$canonicalName] = 0;
			}
			if ( !isset( $unassignedAgg[$canonicalName] ) ) {
				$unassignedAgg[$canonicalName] = 0;
			}
			if ( !isset( $assigned[$nsName] ) ) {
				$assigned[$nsName] = 0;
			}
			if ( !isset( $unassigned[$nsName] ) ) {
				$unassigned[$nsName] = 0;
			}
			$assigned[$nsName] =
				$assigned[$nsName]
				- $collection->get( Collection::ATTR_ASSIGNED_PAGES_AGGREGATED, 0 );
			$unassigned[$nsName] =
				$unassigned[$nsName]
				- $collection->get( Collection::ATTR_UNASSIGNED_PAGES_AGGREGATED, 0 );
		}

		foreach ( $this->namespaces as $idx => $canonicalName ) {
			$data[] = new CollectorRecord( (object)[
				CollectorRecord::NAMESPACE_NAME => $canonicalName,
				CollectorRecord::ASSIGNED => $assigned[$canonicalName],
				CollectorRecord::UNASSIGNED => $unassigned[$canonicalName],
				CollectorRecord::UNASSIGNED_AGGREGATED => $unassignedAgg[$canonicalName],
				CollectorRecord::ASSIGNED_AGGREGATED => $assignedAgg[$canonicalName],
			] );
		}

		return new RecordSet( $data );
	}

	/**
	 *
	 * @return array
	 */
	protected function getFilter() {
		return array_merge( parent::getFilter(), [] );
	}

	/**
	 *
	 * @return array
	 */
	protected function getSort() {
		return [];
	}

	/**
	 *
	 * @param IRecord $record
	 * @return \stdClass
	 */
	protected function map( IRecord $record ) {
		return (object)[
			Collection::ATTR_TYPE => Collection::TYPE,
			Collection::ATTR_NAMESPACE_NAME => $record->get(
				CollectorRecord::NAMESPACE_NAME
			),
			Collection::ATTR_ASSIGNED_PAGES => $record->get(
				CollectorRecord::ASSIGNED
			),
			Collection::ATTR_UNASSIGNED_PAGES => $record->get(
				CollectorRecord::UNASSIGNED
			),
			Collection::ATTR_ASSIGNED_PAGES_AGGREGATED => $record->get(
				CollectorRecord::ASSIGNED_AGGREGATED
			),
			Collection::ATTR_UNASSIGNED_PAGES_AGGREGATED => $record->get(
				CollectorRecord::UNASSIGNED_AGGREGATED
			),
			Collection::ATTR_TIMESTAMP_CREATED => $this->snapshot->get(
				Snapshot::ATTR_TIMESTAMP_CREATED
			),
			Collection::ATTR_TIMESTAMP_TOUCHED => $this->snapshot->get(
				Snapshot::ATTR_TIMESTAMP_TOUCHED
			),
		];
	}

	/**
	 *
	 * @param IRecord $record
	 * @return Collection|null
	 */
	protected function makeCollection( IRecord $record ) {
		$entity = $this->factory->newFromObject( $this->map( $record ) );
		if ( !$entity instanceof Collection ) {
			return null;
		}
		return $entity;
	}

	/**
	 * Class for EntityCollection
	 *
	 * @return string
	 */
	protected function getCollectionClass() {
		return Collection::class;
	}
}

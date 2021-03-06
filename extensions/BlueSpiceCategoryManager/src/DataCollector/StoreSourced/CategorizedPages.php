<?php

namespace BlueSpice\CategoryManager\DataCollector\StoreSourced;

use BlueSpice\CategoryManager\Data\Page\Record;
use BlueSpice\CategoryManager\Data\Page\Store;
use BlueSpice\CategoryManager\Entity\Collection\CategorizedPages as Collection;
use BlueSpice\Data\IRecord;
use BlueSpice\Data\IStore;
use BlueSpice\Data\RecordSet;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollector\StoreSourced\NamespaceCollector;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use Config;
use MediaWiki\MediaWikiServices;
use MWException;
use RequestContext;

class CategorizedPages extends NamespaceCollector {

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
			$context = RequestContext::getMain();
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
			$namespaces = NamespaceCollector::getNamespaces( $snapshot, $services );
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
		$categorized = $uncategorized = $categorizedAgg = $uncategorizedAgg = [];
		foreach ( $this->namespaces as $idx => $canonicalName ) {
			$categorized[$canonicalName] = $uncategorized[$canonicalName]
			= $categorizedAgg[$canonicalName] = $uncategorizedAgg[$canonicalName] = 0;
		}
		foreach ( $res->getRecords() as $record ) {
			if ( !isset( $this->namespaces[(int)$record->get( Record::NS )] ) ) {
				// removed or broken ns
				continue;
			}
			$nsName = $this->namespaces[(int)$record->get( Record::NS )];
			if ( !isset( $categorizedAgg[$nsName] ) ) {
				$categorizedAgg[$nsName] = 0;
			}
			if ( !isset( $uncategorizedAgg[$nsName] ) ) {
				$uncategorizedAgg[$nsName] = 0;
			}
			if ( !isset( $uncategorized[$nsName] ) ) {
				$uncategorized[$nsName] = 0;
			}
			if ( !isset( $categorized[$nsName] ) ) {
				$categorized[$nsName] = 0;
			}
			if ( empty( $record->get( Record::CATEGORIES ) ) ) {
				$uncategorized[$nsName] ++;
				$uncategorizedAgg[$nsName] ++;
			} else {
				$categorized[$nsName]++;
				$categorizedAgg[$nsName] ++;
			}
		}

		$lastCollection = $this->getLastCollection();
		foreach ( $lastCollection as $collection ) {
			$nsName = $collection->get( Collection::ATTR_NAMESPACE_NAME );
			if ( !isset( $categorizedAgg[$canonicalName] ) ) {
				$categorizedAgg[$canonicalName] = 0;
			}
			if ( !isset( $uncategorizedAgg[$canonicalName] ) ) {
				$uncategorizedAgg[$canonicalName] = 0;
			}
			if ( !isset( $categorized[$nsName] ) ) {
				$categorized[$nsName] = 0;
			}
			if ( !isset( $uncategorized[$nsName] ) ) {
				$uncategorized[$nsName] = 0;
			}
			$categorized[$nsName] =
				$categorized[$nsName]
				- $collection->get( Collection::ATTR_CATEGORIZED_PAGES_AGGREGATED, 0 );
			$uncategorized[$nsName] =
				$uncategorized[$nsName]
				- $collection->get( Collection::ATTR_UNCATEGORIZED_PAGES_AGGREGATED, 0 );
		}

		foreach ( $this->namespaces as $idx => $canonicalName ) {
			$data[] = new Record( (object)[
				Collection::ATTR_NAMESPACE_NAME => $canonicalName,
				Collection::ATTR_CATEGORIZED_PAGES => $categorized[$canonicalName],
				Collection::ATTR_UNCATEGORIZED_PAGES => $uncategorized[$canonicalName],
				Collection::ATTR_UNCATEGORIZED_PAGES_AGGREGATED => $uncategorizedAgg[$canonicalName],
				Collection::ATTR_CATEGORIZED_PAGES_AGGREGATED => $categorizedAgg[$canonicalName],
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
				Collection::ATTR_NAMESPACE_NAME
			),
			Collection::ATTR_CATEGORIZED_PAGES => $record->get(
				Collection::ATTR_CATEGORIZED_PAGES
			),
			Collection::ATTR_UNCATEGORIZED_PAGES => $record->get(
				Collection::ATTR_UNCATEGORIZED_PAGES
			),
			Collection::ATTR_CATEGORIZED_PAGES_AGGREGATED => $record->get(
				Collection::ATTR_CATEGORIZED_PAGES_AGGREGATED
			),
			Collection::ATTR_UNCATEGORIZED_PAGES_AGGREGATED => $record->get(
				Collection::ATTR_UNCATEGORIZED_PAGES_AGGREGATED
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

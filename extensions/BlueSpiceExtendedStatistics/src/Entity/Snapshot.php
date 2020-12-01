<?php

namespace BlueSpice\ExtendedStatistics\Entity;

use BlueSpice\Data\Entity\IStore;
use BlueSpice\EntityConfig;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollectorFactory;
use BlueSpice\ExtendedStatistics\ExtendedSearch\Job\Snapshot as Updater;
use BlueSpice\Timestamp;
use MediaWiki\MediaWikiServices;

class Snapshot extends \BlueSpice\Entity {
	const TYPE = 'snapshot';
	const ATTR_COLLECTION = 'collection';

	/**
	 *
	 * @var DataCollectorFactory
	 */
	protected $collectorFactory = null;

	/**
	 *
	 * @param \stdClass $data
	 * @param EntityConfig $config
	 * @param EntityFactory $entityFactory
	 * @param IStore $store
	 * @param DataCollectorFactory|null $collectorFactory
	 */
	protected function __construct( \stdClass $data, EntityConfig $config,
		EntityFactory $entityFactory, IStore $store,
		DataCollectorFactory $collectorFactory ) {
		parent::__construct( $data, $config, $entityFactory, $store );

		$this->collectorFactory = $collectorFactory;

		if ( !empty( $data->{static::ATTR_COLLECTION} ) ) {
			$this->attributes[static::ATTR_COLLECTION] = $this->makeCollection(
				$data->{static::ATTR_COLLECTION}
			);
		} elseif ( !$this->exists() ) {
			$this->attributes[static::ATTR_COLLECTION] = $this->collect();
		}
	}

	/**
	 * Returns the instance - Should not be used directly. Use mediawiki service
	 * 'BSEntityFactory' instead
	 * @param \stdClass $data
	 * @param EntityConfig $config
	 * @param IStore $store
	 * @param EntityFactory|null $entityFactory
	 * @param DataCollectorFactory|null $collectorFactory
	 * @return \static
	 */
	public static function newFromFactory( \stdClass $data, EntityConfig $config,
		IStore $store, EntityFactory $entityFactory = null,
		DataCollectorFactory $collectorFactory = null ) {
		if ( !$entityFactory ) {
			$entityFactory = MediaWikiServices::getInstance()->getService(
				'BSEntityFactory'
			);
		}
		if ( !$collectorFactory ) {
			$collectorFactory = MediaWikiServices::getInstance()->getService(
				'BSExtendedStatisticsDataCollectorFactory'
			);
		}
		return new static( $data, $config, $entityFactory, $store, $collectorFactory );
	}

	/**
	 *
	 * @return Collection[]
	 */
	protected function collect() {
		$collection = [];
		foreach ( $this->collectorFactory->getCollectors( $this ) as $collector ) {
			$collected = $collector->collect();
			if ( !$collected ) {
				continue;
			}
			$collection = array_merge( $collection, $collected );
		}
		return $collection;
	}

	/**
	 *
	 * @param \StdClass $rawData
	 * @return Collection[]
	 */
	private function makeCollection( $rawData ) {
		$collection = [];
		foreach ( $rawData as $type => $data ) {
			$data = (object)$data;
			if ( isset( $data->{Collection::ATTR_ID} ) ) {
				unset( $data->{Collection::ATTR_ID} );
			}
			$entity = $this->entityFactory->newFromObject( $data );
			if ( !$entity instanceof Collection ) {
				continue;
			}
			if ( $this->exists() ) {
				$entity->set( Collection::ATTR_ID, $this->get( static::ATTR_ID ) );
			}
			$collection[$type] = $entity;
		}
		return $collection;
	}

	/**
	 * Gets the Entity attributes formatted for the api
	 * @param array $data
	 * @return array
	 */
	public function getFullData( $data = [] ) {
		$collectionData = [];
		foreach ( $this->get( static::ATTR_COLLECTION, [] ) as $entity ) {
			$collectionData[] = $entity->getFullData();
		}
		$data = array_merge( $data, [
			static::ATTR_COLLECTION => $collectionData,
		] );
		return parent::getFullData( $data );
	}

	/**
	 * @param \stdClass $data
	 */
	public function setValuesByObject( \stdClass $data ) {
		if ( !empty( $data->{static::ATTR_TIMESTAMP_CREATED} ) ) {
			$this->set( static::ATTR_TIMESTAMP_CREATED, $data->{static::ATTR_TIMESTAMP_CREATED} );
		}
		if ( !empty( $data->{static::ATTR_TIMESTAMP_TOUCHED} ) ) {
			$this->set( static::ATTR_TIMESTAMP_TOUCHED, $data->{static::ATTR_TIMESTAMP_TOUCHED} );
		}

		parent::setValuesByObject( $data );
	}

	/**
	 * Returns an entity's attributes or the given default, if not set
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		if ( !isset( $this->attributes[$attrName] ) ) {
			if ( $attrName === static::ATTR_TIMESTAMP_TOUCHED ) {
				return $this->get( static::ATTR_TIMESTAMP_CREATED, $default );
			}
			if ( $attrName === static::ATTR_TIMESTAMP_CREATED ) {
				return ( new Timestamp() )->getTimestamp( TS_MW );
			}
		}
		return parent::get( $attrName, $default );
	}

	/**
	 * Invalidate the cache
	 * @return Entity
	 */
	public function invalidateCache() {
		// run this directly from this class, as it seems, that the
		// ExtendedSearch/Updater just refuses to do anything
		$title = \SpecialPage::getTitleFor(
			'ExtendedStatisticsSnapshots',
			$this->get( static::ATTR_ID, 0 )
		);
		$job = new Updater( $title, [ 'entity' => [
			static::ATTR_TYPE => $this->get( static::ATTR_TYPE ),
			static::ATTR_ID => $this->get( static::ATTR_ID ),
		] ] );
		try {
			$res = $job->run();
		} catch ( \Exception $e ) {
		}
		return $this;
	}

}

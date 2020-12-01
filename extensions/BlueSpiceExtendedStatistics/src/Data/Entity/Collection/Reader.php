<?php

namespace BlueSpice\ExtendedStatistics\Data\Entity\Collection;

use BlueSpice\Data\Entity\Reader as EntityReader;
use BlueSpice\Data\ResultSet;
use BlueSpice\EntityConfig;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\Data\Aggregator;
use BlueSpice\ExtendedStatistics\Data\ReaderParams;
use BS\ExtendedSearch\Backend;
use Config;
use IContextSource;

class Reader extends EntityReader {

	/**
	 *
	 * @var Backend
	 */
	protected $searchBackend = null;

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param Backend $searchBackend
	 * @param EntityFactory $factory
	 * @param IContextSource|null $context
	 * @param Config|null $config
	 */
	public function __construct( Backend $searchBackend, $factory,
		IContextSource $context = null, Config $config = null ) {
		parent::__construct( $context, $config );
		$this->searchBackend = $searchBackend;
		$this->factory = $factory;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->searchBackend,
			$this->getSchema(),
			$this->factory,
			$this->context
		);
	}

	/**
	 *
	 * @return SecondaryDataProvider|null
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return ResultSet
	 */
	public function read( $params ) {
		$primaryDataProvider = $this->makePrimaryDataProvider( $params );
		$dataSets = $primaryDataProvider->makeData( $params );

		$filterer = $this->makeFilterer( $params );
		$dataSets = $filterer->filter( $dataSets );

		$aggregator = $this->makeAggregator( $params );
		$dataSets = $aggregator->aggregate( $dataSets );

		$total = count( $dataSets );

		$sorter = $this->makeSorter( $params );
		$dataSets = $sorter->sort(
			$dataSets,
			$this->getSchema()->getUnsortableFields()
		);

		$trimmer = $this->makeTrimmer( $params );
		$dataSets = $trimmer->trim( $dataSets );

		$secondaryDataProvider = $this->makeSecondaryDataProvider();
		if ( $secondaryDataProvider instanceof ISecondaryDataProvider ) {
			$dataSets = $secondaryDataProvider->extend( $dataSets );
		}

		$resultSet = new ResultSet( $dataSets, $total );
		return $resultSet;
	}

	/**
	 *
	 * @param int $id
	 * @param EntityConfig $entityConfig
	 * @return \stdClass
	 */
	public function resolveNativeDataFromID( $id, EntityConfig $entityConfig ) {
		return new \stdClass;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return Aggregator
	 */
	protected function makeAggregator( $params ) {
		return new Aggregator( $params->getAggregate() );
	}

}

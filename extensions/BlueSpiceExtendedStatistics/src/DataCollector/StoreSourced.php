<?php

namespace BlueSpice\ExtendedStatistics\DataCollector;

use BlueSpice\Data\IStore;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\RecordSet;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollector;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use Config;

abstract class StoreSourced extends DataCollector {
	/**
	 *
	 * @var IStore
	 */
	protected $store = null;

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 * @param IStore $store
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory, IStore $store ) {
		parent::__construct( $type, $snapshot, $config, $factory );
		$this->store = $store;
	}

	/**
	 *
	 * @return RecordSet
	 */
	protected function doCollect() {
		return $this->store->getReader()->read( $this->getReaderParams() );
	}

	/**
	 *
	 * @return ReaderParams
	 */
	protected function getReaderParams() {
		return new ReaderParams( [
			ReaderParams::PARAM_LIMIT => $this->getLimit(),
			ReaderParams::PARAM_FILTER => $this->getFilter(),
			ReaderParams::PARAM_SORT => $this->getSort(),
			ReaderParams::PARAM_QUERY => $this->getQuery(),
			ReaderParams::PARAM_START => $this->getStart()
		] );
	}

	/**
	 *
	 * @return int
	 */
	protected function getLimit() {
		return ReaderParams::LIMIT_INFINITE;
	}

	/**
	 *
	 * @return array
	 */
	protected function getFilter() {
		return [];
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
	 * @return string
	 */
	protected function getQuery() {
		return '';
	}

	/**
	 *
	 * @return int
	 */
	protected function getStart() {
		return 0;
	}

}

<?php

namespace BlueSpice\ExtendedStatistics\Data;

use BlueSpice\Data\ReaderParams as Params;

class ReaderParams extends Params {
	const PARAM_AGGREGATOR = 'aggregate';

	/**
	 *
	 * @var Aggregate[]
	 */
	protected $aggregate = [];

	/**
	 *
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		parent::__construct( $params );
		$this->setAggregate( $params );
	}

	/**
	 * Getter for "aggregate" param
	 * @return Aggregate[]
	 */
	public function getAggregate() {
		return $this->aggregate;
	}

	/**
	 *
	 * @param array $params
	 * @return void
	 */
	protected function setAggregate( $params ) {
		if ( !isset( $params[static::PARAM_AGGREGATOR] )
			|| !is_array( $params[static::PARAM_AGGREGATOR] ) ) {
			return;
		}
		$this->aggregate = Aggregate::newCollectionFromArray(
			$params[static::PARAM_AGGREGATOR]
		);
	}

}

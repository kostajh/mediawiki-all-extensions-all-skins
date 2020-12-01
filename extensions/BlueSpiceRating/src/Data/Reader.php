<?php

namespace BlueSpice\Rating\Data;

use BlueSpice\Data\DatabaseReader;

class Reader extends DatabaseReader {
	/**
	 *
	 * @param \LoadBalancer $loadBalancer
	 * @param \IContextSource|null $context
	 */
	public function __construct( $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db );
	}

	/**
	 *
	 * @return null
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
	 * @return RatingSet
	 */
	public function read( $params ) {
		$result = parent::read( $params );
		$records = [];
		foreach ( $result->getRecords() as $record ) {
			$records[] = $record;
		}
		$setClass = $this->config->get( 'RatingSetClass' );
		return new $setClass( $records, $result->getTotal() );
	}

}

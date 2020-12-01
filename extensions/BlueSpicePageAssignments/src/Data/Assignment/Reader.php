<?php

namespace BlueSpice\PageAssignments\Data\Assignment;

use BlueSpice\Data\DatabaseReader;
use BlueSpice\Data\ReaderParams;

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
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $params, $this->db );
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
	 * @return \BlueSpice\PageAssignments\Data\Schema
	 */
	public function getSchema() {
		return new \BlueSpice\PageAssignments\Data\Schema();
	}

}

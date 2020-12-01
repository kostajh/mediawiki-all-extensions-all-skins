<?php

namespace BlueSpice\Readers\Data\RevisionReaders;

use BlueSpice\Readers\Data\Record;

class Writer extends \BlueSpice\Data\DatabaseWriter {

	/**
	 *
	 * @param \BlueSpice\Data\IReader $reader
	 * @param \LoadBalancer $loadBalancer
	 * @param \IContextSource|null $context
	 */
	public function __construct( \BlueSpice\Data\IReader $reader, $loadBalancer,
		\IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return 'bs_readers';
	}

	/**
	 *
	 * @return \BlueSpice\Readers\Data\Schema
	 */
	public function getSchema() {
		return new \BlueSpice\Readers\Data\Schema();
	}

	/**
	 *
	 * @return string[]
	 */
	protected function getIdentifierFields() {
		return [ Record::REV_ID, Record::USER_ID ];
	}
}

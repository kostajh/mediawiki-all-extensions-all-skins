<?php

namespace BlueSpice\Rating\Data;

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
		return 'bs_rating';
	}

	/**
	 * @return Schema Column definition compatible to
	 * https://docs.sencha.com/extjs/4.2.1/#!/api/Ext.grid.Panel-cfg-columns
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return array
	 */
	protected function getIdentifierFields() {
		return [ Record::ID ];
	}
}

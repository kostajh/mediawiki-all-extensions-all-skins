<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\Data\RecordSet;

class Writer extends \BlueSpice\Data\Entity\Writer\Content {

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param RecordSet $dataSet
	 * @return RecordSet
	 */
	public function remove( $dataSet ) {
		throw new Exception( 'Removing entity store is not supported yet' );
	}
}

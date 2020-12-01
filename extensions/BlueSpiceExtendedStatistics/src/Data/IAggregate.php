<?php

namespace BlueSpice\ExtendedStatistics\Data;

use BlueSpice\Data\Record;

interface IAggregate {
	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	public function applies( $value, $value2 );

	/**
	 *
	 * @param Record &$targetDataSet
	 * @param Record $dataSet
	 */
	public function merge( &$targetDataSet, $dataSet );
}

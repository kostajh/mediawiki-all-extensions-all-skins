<?php

namespace BlueSpice\ExtendedStatistics\Data\Aggregate;

use BlueSpice\ExtendedStatistics\Data\Aggregate;

class Numeric extends Aggregate {
	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	protected function doesApply( $value, $value2 ) {
		if ( !is_numeric( $value ) || !is_numeric( $value2 ) ) {
			// TODO: Warning
			return false;
		}
		return (int)$value == (int)$value2;
	}
}

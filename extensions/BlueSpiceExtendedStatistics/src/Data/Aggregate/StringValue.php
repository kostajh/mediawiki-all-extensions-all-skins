<?php

namespace BlueSpice\ExtendedStatistics\Data\Aggregate;

use BlueSpice\ExtendedStatistics\Data\Aggregate;

/**
 * Class name "String" is reserved
 */
class StringValue extends Aggregate {

	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	protected function doesApply( $value, $value2 ) {
		if ( !is_string( $value ) || !is_string( $value2 ) ) {
			// TODO: Warning
			return false;
		}
		return (string)$value == (string)$value2;
	}
}

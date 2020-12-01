<?php

namespace BlueSpice\ExtendedStatistics\Data\Aggregate;

use BlueSpice\ExtendedStatistics\Data\Aggregate;

class Boolean extends Aggregate {

	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	protected function doesApply( $value, $value2 ) {
		return ( $value && $value2 ) || ( !$value && !$value2 );
	}
}

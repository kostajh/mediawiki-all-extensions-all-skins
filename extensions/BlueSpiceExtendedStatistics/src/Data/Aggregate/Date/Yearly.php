<?php

namespace BlueSpice\ExtendedStatistics\Data\Aggregate\Date;

use BlueSpice\ExtendedStatistics\Data\Aggregate\Date;

class Yearly extends Date {

	/**
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	protected function doesApply( $value, $value2 ) {
		return $this->makeTimestamp( $value )->timestamp->format( 'Y' )
			== $this->makeTimestamp( $value2 )->timestamp->format( 'Y' );
	}
}

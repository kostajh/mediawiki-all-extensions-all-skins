<?php

namespace BlueSpice\ExtendedStatistics\Data\Aggregate;

use BlueSpice\ExtendedStatistics\Data\Aggregate;
use BlueSpice\Timestamp;
use DateTime;
use DateTimeZone;

class Date extends Aggregate {

	/**
	 *
	 * @param string $value
	 * @return Timestamp
	 */
	protected function makeTimestamp( $value ) {
		$ts = DateTime::createFromFormat(
			'YmdHis',
			$value,
			new DateTimeZone( 'UTC' )
		);
		return new Timestamp( $ts );
	}

	/**
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	protected function doesApply( $value, $value2 ) {
		return $this->makeTimestamp( $value )->timestamp
			== $this->makeTimestamp( $value2 )->timestamp;
	}
}

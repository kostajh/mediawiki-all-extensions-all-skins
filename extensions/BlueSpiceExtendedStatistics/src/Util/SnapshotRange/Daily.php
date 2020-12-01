<?php

namespace BlueSpice\ExtendedStatistics\Util\SnapshotRange;

use BlueSpice\ExtendedStatistics\Util\ISnapshotRange;
use BlueSpice\Timestamp;

class Daily implements ISnapshotRange {

	/**
	 *
	 * @var Timestamp
	 */
	protected $timestamp = null;

	/**
	 *
	 * @var Timestamp
	 */
	protected $start = null;

	/**
	 *
	 * @var Timestamp
	 */
	protected $end = null;

	/**
	 *
	 * @param Timestamp|null $timestamp
	 */
	public function __construct( Timestamp $timestamp = null ) {
		if ( !$timestamp ) {
			$timestamp = new Timestamp;
		}
		$this->timestamp = $timestamp;
	}

	/**
	 *
	 * @return Timestamp
	 */
	public function getStart() {
		if ( $this->start ) {
			return $this->start;
		}
		$this->start = clone $this->timestamp;
		$this->start->timestamp->setTime( 0, 0, 0 );
		return $this->start;
	}

	/**
	 *
	 * @return Timestamp
	 */
	public function getEnd() {
		if ( $this->end ) {
			return $this->end;
		}
		$this->end = clone $this->timestamp;
		$this->end->timestamp->setTime( 23, 59, 59 );
		return $this->end;
	}
}

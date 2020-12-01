<?php

namespace BlueSpice\ExtendedStatistics\Util;

use BlueSpice\Timestamp;

interface ISnapshotRange {

	/**
	 * @return Timestamp
	 */
	public function getStart();

	/**
	 * @return Timestamp
	 */
	public function getEnd();
}

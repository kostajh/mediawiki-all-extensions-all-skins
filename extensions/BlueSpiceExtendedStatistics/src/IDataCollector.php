<?php

namespace BlueSpice\ExtendedStatistics;

use BlueSpice\ExtendedStatistics\Entity\Collection;

interface IDataCollector {
	/**
	 * @return Collection[]
	 */
	public function collect();
}

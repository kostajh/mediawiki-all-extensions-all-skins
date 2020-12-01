<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch\Updater;

use BS\ExtendedSearch\Source\Updater\Base as Updater;

class Snapshot extends Updater {
	protected $sJobClass = "\\BlueSpice\\ExtendedStatistics\\ExtendedSearch\\Job\\Snapshot";

	/**
	 *
	 * @param array &$aHooks
	 */
	public function init( &$aHooks ) {
	}
}

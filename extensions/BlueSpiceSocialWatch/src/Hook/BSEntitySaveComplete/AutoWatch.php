<?php

namespace BlueSpice\Social\Watch\Hook\BSEntitySaveComplete;

use BlueSpice\Hook\BSEntitySaveComplete;
use BlueSpice\Social\Entity;

class AutoWatch extends BSEntitySaveComplete {

	protected function doProcess() {
		if ( !$this->entity instanceof Entity ) {
			return true;
		}

		$autoWatcherFactory = $this->getServices()->getService( 'BSSocialAutoWatcherFactory' );
		$autoWatcher = $autoWatcherFactory->factory( $this->entity, $this->getContext() );
		$autoWatcher->autoWatch();

		return true;
	}
}

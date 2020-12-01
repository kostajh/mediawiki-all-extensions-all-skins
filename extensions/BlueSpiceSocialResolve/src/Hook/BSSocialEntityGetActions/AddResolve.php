<?php

namespace BlueSpice\Social\Resolve\Hook\BSSocialEntityGetActions;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Hook\BSSocialEntityGetActions;

class AddResolve extends BSSocialEntityGetActions {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->aActions['resolved'] = [];
		return true;
	}

	/**
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->oEntity instanceof Entity ) {
			return true;
		}
		if ( !$this->oEntity->getConfig()->get( 'IsResolvable' ) ) {
			return true;
		}
		if ( !$this->oEntity->exists() ) {
			return true;
		}

		return false;
	}
}

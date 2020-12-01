<?php

namespace BlueSpice\Social\Tags\Hook\BSSocialEntityGetActions;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Hook\BSSocialEntityGetActions;

class AddTags extends BSSocialEntityGetActions {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->aActions['tags'] = [];
		return true;
	}

	/**
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->oEntity instanceof Entity ) {
			return true;
		}
		if ( !$this->oEntity->exists() || $this->oEntity->hasParent() ) {
			return true;
		}
		if ( !$this->oEntity->getConfig()->get( 'IsTagable' ) ) {
			return true;
		}

		$status = $this->oEntity->userCan( 'tag' );
		if ( !$status->isOK() ) {
			return true;
		}

		return false;
	}
}

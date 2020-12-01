<?php

namespace BlueSpice\Social\Watch\Hook\BSSocialEntityGetActions;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Hook\BSSocialEntityGetActions;

class AddWatch extends BSSocialEntityGetActions {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->aActions['watch'] = [];
		return true;
	}

	/**
	 * @return bool
	 */
	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		if ( !$user || $user->isAnon() ) {
			return true;
		}

		$entity = $this->oEntity;
		if ( !$entity instanceof Entity ) {
			return true;
		}
		if ( !$entity->exists() || $entity->hasParent() ) {
			return true;
		}
		if ( !$entity->getConfig()->get( 'IsWatchable' ) ) {
			return true;
		}

		return false;
	}
}

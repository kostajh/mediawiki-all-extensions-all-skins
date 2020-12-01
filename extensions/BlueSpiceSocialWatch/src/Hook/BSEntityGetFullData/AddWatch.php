<?php

namespace BlueSpice\Social\Watch\Hook\BSEntityGetFullData;

use BlueSpice\Hook\BSEntityGetFullData;
use BlueSpice\Social\Entity;

class AddWatch extends BSEntityGetFullData {

	protected function checkEntity() {
		if ( !$this->entity instanceof Entity ) {
			return false;
		}
		if ( !$this->entity->getTitle()->isWatchable() ) {
			return false;
		}
		if ( !$this->entity->getConfig()->get( 'IsWatchable' ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->checkEntity() ) {
			return true;
		}

		$this->data['watch'] = false;
		$oUser = $this->getContext()->getUser();
		if ( !$oUser || $oUser->isAnon() ) {
			return true;
		}
		$this->data['watch'] = $oUser->isWatched(
			$this->entity->getTitle(),
			\User::IGNORE_USER_RIGHTS
		);
		return true;
	}
}

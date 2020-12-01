<?php

namespace BlueSpice\Avatars\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

/**
 * Adds style and script modules
 */
class AddModules extends BeforePageDisplay {

	protected function skipProcessing() {
		$user = $this->out->getUser();
		if ( !$user || $user->isAnon() ) {
			return true;
		}
		if ( !$this->out->getTitle()->equals( $user->getUserPage() ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->out->addModules( "ext.bluespice.avatars.js" );
	}

}

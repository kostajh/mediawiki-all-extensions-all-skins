<?php

namespace BlueSpice\WhoIsOnline\Hook\BeforePageDisplay;

class AddModules extends \BlueSpice\Hook\BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return !\MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'read', $this->out->getUser(), $this->out->getTitle() );
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.whoisonline' );
		return true;
	}

}

<?php

namespace BlueSpice\Social\Profile\Hook\UserLoadAfterLoadFromSession;

use BlueSpice\Hook\UserLoadAfterLoadFromSession;

class AddLDAPProfileDataSync extends UserLoadAfterLoadFromSession {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$uInfo = "\MediaWiki\Extension\LDAPUserInfo";
		return !class_exists(
			"$uInfo\Hook\UserLoadAfterLoadFromSession\SyncUserInfoAfterLoadSession"
		);
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$handler = new LDAPProfileDataSync(
			$this->getContext(),
			$this->getConfig(),
			$this->user,
			$this->getServices()->getService( 'BSSocialProfileEntityFactory' ),
			$this->getServices()->getService( 'BSSocialProfileCustomFieldsFactory' )
		);
		return $handler->process();
	}

}

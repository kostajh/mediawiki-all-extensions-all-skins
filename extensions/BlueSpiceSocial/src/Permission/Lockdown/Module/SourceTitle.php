<?php

namespace BlueSpice\Social\Permission\Lockdown\Module;

use MediaWiki\MediaWikiServices;
use Message;
use Title;
use User;

class SourceTitle extends \BlueSpice\Permission\Lockdown\Module {

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function applies( Title $title, User $user ) {
		return $title->getNamespace() === NS_SOCIALENTITY;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return bool
	 */
	public function mustLockdown( Title $title, User $user, $action ) {
		if ( $action === 'read' ) {
			return false;
		}
		// No one should be able to modify this articles besides a sysop
		if ( $action === 'wikiadmin' ) {
			return false;
		}
		return !MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$user,
			'wikiadmin'
		);
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return Message
	 */
	public function getLockdownReason( Title $title, User $user, $action ) {
		$actionMsg = $this->msg( "right-$action" );
		return $this->msg(
			'bs-social-sourcetitle-lockdown-reason-reason',
			$actionMsg->exists() ? $actionMsg : $action
		);
	}

}

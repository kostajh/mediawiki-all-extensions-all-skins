<?php

namespace BlueSpice\ContextMenu\MenuItem;

use RequestContext;
use SpecialEmailUser;
use SpecialPage;

class MailUser extends BaseUserAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'icon-message';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-user-mail' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return SpecialPage::getTitleFor( 'Movepage' )->getFullURL() . '/' . $this->title->getFullText();
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-move';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		if ( $this->targetUser ) {
			$user = $this->getUser();
			$eMailPermissioErrors = SpecialEmailUser::getPermissionsError(
					$user, $user->getEditToken()
			);
			return $eMailPermissioErrors;
		}
		return false;
	}

	/**
	 *
	 * @return \User
	 */
	public function getUser() {
		$context = RequestContext::getMain();
		return $context->getUser();
	}

}

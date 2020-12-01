<?php

namespace BlueSpice\ContextMenu\MenuItem;

class History extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-history';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-history' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'history' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-history';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $context->getUser(), $this->title );
	}

}

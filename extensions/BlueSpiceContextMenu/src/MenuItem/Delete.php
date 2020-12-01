<?php

namespace BlueSpice\ContextMenu\MenuItem;

class Delete extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-bin';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-delete' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'delete' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-delete';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return $this->title->exists() &&
			\MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'delete', $context->getUser(), $this->title );
	}

}

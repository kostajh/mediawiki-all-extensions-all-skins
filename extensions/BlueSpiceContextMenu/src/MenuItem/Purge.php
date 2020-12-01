<?php

namespace BlueSpice\ContextMenu\MenuItem;

class Purge extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-purge';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-purge' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'purge' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-purge';
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
				->userCan( 'purge', $context->getUser(), $this->title );
	}

}

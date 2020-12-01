<?php

namespace BlueSpice\ContextMenu\MenuItem;

class Watch extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-magnifying-glass';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'watchthis' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'watch' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-watch';
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
				->userCan( 'read', $context->getUser(), $this->title );
	}

}

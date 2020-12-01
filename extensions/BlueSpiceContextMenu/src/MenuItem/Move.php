<?php

namespace BlueSpice\ContextMenu\MenuItem;

use SpecialPage;

class Move extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-shuffle';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-move' );
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
	 * @return type
	 */
	public function shouldList( $context ) {
		return \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'move', $context->getUser(), $this->title ) && $this->title->isMovable();
	}

}

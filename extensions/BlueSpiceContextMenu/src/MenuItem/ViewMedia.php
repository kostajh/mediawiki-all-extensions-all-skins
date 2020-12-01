<?php

namespace BlueSpice\ContextMenu\MenuItem;

class ViewMedia extends BaseFileAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-text';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-media-view-page' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl();
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-viewmediapage';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		if ( $this->file ) {
			if ( $this->file->exists() ) {
				return \MediaWiki\MediaWikiServices::getInstance()
					->getPermissionManager()
					->userCan( 'read', $context->getUser(), $this->title );
			}
		}
		return false;
	}

}

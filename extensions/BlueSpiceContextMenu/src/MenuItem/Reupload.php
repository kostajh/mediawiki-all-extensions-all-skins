<?php

namespace BlueSpice\ContextMenu\MenuItem;

class Reupload extends BaseFileAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-upload';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-media-reupload' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return \SpecialPage::getTitleFor( 'Upload' )->
			getLocalURL( [ 'wpDestFile' => $this->title->getText() ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-reupload';
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
					->userCan( 'reupload', $context->getUser(), $this->title );
			}
		}
		return false;
	}

}

<?php

namespace BlueSpice\ContextMenu\MenuItem;

class Whatlinkshere extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'bs-icon-earth';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'whatlinkshere' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return \SpecialPage::getTitleFor( 'Whatlinkshere' )->
			getLinkURL( [ 'target' => $this->title->getFullText() ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-whatlinkshere';
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

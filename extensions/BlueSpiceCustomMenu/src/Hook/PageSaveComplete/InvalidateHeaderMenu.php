<?php

namespace BlueSpice\CustomMenu\Hook\PageSaveComplete;

class InvalidateHeaderMenu extends \BlueSpice\Hook\PageSaveComplete {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		if ( !$this->wikiPage->getTitle()->equals( $title ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$menu = $this->getServices()->getService( 'BSCustomMenuFactory' )
			->getMenu( 'header' );
		if ( !$menu ) {
			return true;
		}
		$menu->invalidate();
		return true;
	}

}

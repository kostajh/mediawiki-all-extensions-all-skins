<?php

namespace BlueSpice\Bookshelf\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Bookshelf\Panel\BookNav;
use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddBookshelfUI extends ChameleonSkinTemplateOutputPageBeforeExec {
	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->addSiteNavTab();
		$this->addGlobalActions();

		return true;
	}

	protected function addGlobalActions() {
		$bookManager = $this->getServices()
			->getSpecialPageFactory()
			->getPage( 'BookshelfBookManager' );
		$pm = $this->getServices()->getPermissionManager();
		$userHasRight = $pm->userHasRight(
			$this->getContext()->getUser(),
			$bookManager->getRestriction()
		);
		if ( !$userHasRight ) {
			return;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-bookshelfui-bookmanager' => [
					'href' => $bookManager->getPageTitle()->getFullURL(),
					'text' => wfMessage( 'bookshelfbookmanager' )->plain(),
					'title' => wfMessage( 'bs-bookshelfui-extension-description' ),
					'iconClass' => 'icon-books'
				]
			]
		);
	}

	protected function addSiteNavTab() {
		$this->mergeSkinDataArray(
			SkinData::SITE_NAV,
			[
				'bs-bookshelf' => [
					'position' => 30,
					'callback' => function ( $sktemplate ) {
						return new BookNav( $sktemplate );
					}
				]
			]
		);
	}

}

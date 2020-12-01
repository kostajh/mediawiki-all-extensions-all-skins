<?php

namespace BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$this->addSpecialPages();
		$this->addSpecialUpload();
		$this->addSpecialWatchlist();
		return true;
	}

	private function addSpecialPages() {
		$factory = $this->getServices()->getSpecialPageFactory();
		$special = $factory->getPage( 'Specialpages' );
		if ( !$special ) {
			return;
		}
		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$special->getRestriction()
		);
		if ( !$isAllowed ) {
			return;
		}
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'specialpage-specialpages' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'iconClass' => ' icon-special-specialpages ',
					'position' => 20
				]
			]
		);
	}

	private function addSpecialUpload() {
		$factory = $this->getServices()->getSpecialPageFactory();
		$special = $factory->getPage( 'Upload' );
		if ( !$special ) {
			return;
		}
		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$special->getRestriction()
		);
		if ( !$isAllowed ) {
			return;
		}
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'specialpage-upload' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'iconClass' => ' icon-special-upload ',
					'position' => 10
				]
			]
		);
	}

	private function addSpecialWatchlist() {
		$factory = $this->getServices()->getSpecialPageFactory();
		$special = $factory->getPage( 'Watchlist' );
		if ( !$special ) {
			return;
		}

		if ( $this->getContext()->getUser()->isAnon() ) {
			return;
		}

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$special->getRestriction()
		);
		if ( !$isAllowed ) {
			return;
		}
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'specialpage-watchlist' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'iconClass' => ' icon-special-watchlist ',
					'position' => 30
				]
			]
		);
	}
}

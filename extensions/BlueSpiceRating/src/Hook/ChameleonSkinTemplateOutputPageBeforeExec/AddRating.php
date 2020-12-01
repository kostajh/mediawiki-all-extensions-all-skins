<?php

namespace BlueSpice\Rating\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddRating extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$specialRating = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Rating' );

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$specialRating->getRestriction()
		);
		if ( !$isAllowed ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-special-rating' => [
					'href' => $specialRating->getPageTitle()->getFullURL(),
					'text' => $specialRating->getDescription(),
					'title' => $specialRating->getPageTitle(),
					'iconClass' => ' icon-special-rating ',
					'position' => 30
				]
			]
		);

		return true;
	}
}

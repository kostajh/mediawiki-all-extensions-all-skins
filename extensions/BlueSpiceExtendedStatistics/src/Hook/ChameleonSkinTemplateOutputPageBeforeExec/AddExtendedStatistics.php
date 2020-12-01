<?php

namespace BlueSpice\ExtendedStatistics\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddExtendedStatistics extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialExtendedStatistic = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'ExtendedStatistics' );

		if ( !$oSpecialExtendedStatistic ) {
			return true;
		}

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$oSpecialExtendedStatistic->getRestriction()
		);
		if ( !$isAllowed ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-extended-statistics' => [
					'href' => $oSpecialExtendedStatistic->getPageTitle()->getFullURL(),
					'text' => $oSpecialExtendedStatistic->getDescription(),
					'title' => $oSpecialExtendedStatistic->getPageTitle(),
					'iconClass' => ' icon-statistics ',
					'position' => 700,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}

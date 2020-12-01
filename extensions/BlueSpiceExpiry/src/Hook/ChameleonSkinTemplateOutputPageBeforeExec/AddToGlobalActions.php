<?php

namespace BlueSpice\Expiry\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialExpiry = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Expiry' );

		if ( !$oSpecialExpiry ) {
			return true;
		}

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$oSpecialExpiry->getRestriction()
		);
		if ( !$isAllowed ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-expiry' => [
					'href' => $oSpecialExpiry->getPageTitle()->getFullURL(),
					'text' => $oSpecialExpiry->getDescription(),
					'title' => $oSpecialExpiry->getPageTitle(),
					'iconClass' => ' icon-hour-glass ',
					'position' => 800,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}

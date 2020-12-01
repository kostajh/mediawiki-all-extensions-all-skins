<?php

namespace BlueSpice\Rating\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddRecommendationsGlobalAction extends ChameleonSkinTemplateOutputPageBeforeExec {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$special = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Recommendations' );
		if ( !$special ) {
			return true;
		}
		if ( !$special->userCanExecute( $this->skin->getUser() ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$special = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Recommendations' );
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-special-recommendations' => [
					'href' => $special->getPageTitle()->getFullURL(),
					'text' => $special->getDescription(),
					'title' => $special->getPageTitle(),
					'iconClass' => ' icon-special-recommendations ',
					'position' => 30
				]
			]
		);

		return true;
	}
}

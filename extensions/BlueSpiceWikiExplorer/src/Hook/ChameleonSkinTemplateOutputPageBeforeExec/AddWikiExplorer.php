<?php

namespace BlueSpice\WikiExplorer\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddWikiExplorer extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$specialWikiExplorer = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'WikiExplorer' );
		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
			'bs-special-wikiexplorer' => [
				'href' => $specialWikiExplorer->getPageTitle()->getFullURL(),
				'text' => $specialWikiExplorer->getDescription(),
				'title' => $specialWikiExplorer->getPageTitle(),
				'iconClass' => ' icon-world ',
				'position' => 30
			]
			]
		);

		return true;
	}

}

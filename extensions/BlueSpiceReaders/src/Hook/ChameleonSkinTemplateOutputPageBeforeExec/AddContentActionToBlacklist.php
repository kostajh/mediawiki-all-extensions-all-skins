<?php

namespace BlueSpice\Readers\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddContentActionToBlacklist extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'readers' );
		return true;
	}
}

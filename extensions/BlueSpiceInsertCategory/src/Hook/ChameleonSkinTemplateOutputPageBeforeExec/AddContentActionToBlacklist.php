<?php

namespace BlueSpice\InsertCategory\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddContentActionToBlacklist extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'insert_category' );
		return true;
	}
}

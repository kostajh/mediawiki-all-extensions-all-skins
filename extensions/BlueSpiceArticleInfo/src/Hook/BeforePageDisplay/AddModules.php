<?php

namespace BlueSpice\ArticleInfo\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.articleinfo.general' );
		$this->out->addModuleStyles( 'ext.bluespice.articleinfo.flyout.styles' );
		return true;
	}

}

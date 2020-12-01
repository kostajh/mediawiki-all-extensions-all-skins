<?php

namespace BlueSpice\WikiExplorer\Hook\BeforePageDisplay;

class AddModuleStyles extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.wikiExplorer.styles' );
		return true;
	}

}

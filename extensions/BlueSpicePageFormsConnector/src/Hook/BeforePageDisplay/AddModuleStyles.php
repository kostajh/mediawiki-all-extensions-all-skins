<?php

namespace BlueSpice\PageFormsConnector\Hook\BeforePageDisplay;

class AddModuleStyles extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$title = $this->skin->getTitle();

		if ( $title->isSpecial( 'FormEdit' ) ) {
			$this->out->addModuleStyles( 'ext.bluespice.pageformsconnector.styles' );
		}

		return true;
	}
}

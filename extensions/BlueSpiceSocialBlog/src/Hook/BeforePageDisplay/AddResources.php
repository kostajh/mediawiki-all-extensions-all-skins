<?php

namespace BlueSpice\Social\Blog\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.socialblog.styles' );
		return true;
	}

}

<?php

namespace BlueSpice\QrCode\Hook\BeforePageDisplay;

class AddRessources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.qrcode.styles' );

		return true;
	}
}

<?php

namespace BlueSpice\Calumma\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddFoucFix extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addInlineStyle( 'html{visibility: hidden;opacity:0.5;}' );
		$this->out->addModuleStyles( 'skin.bluespicecalumma.foucfix' );
		return true;
	}

}

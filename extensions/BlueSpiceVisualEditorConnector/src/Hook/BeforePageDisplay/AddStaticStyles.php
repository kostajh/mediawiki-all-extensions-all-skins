<?php

namespace BlueSpice\VisualEditorConnector\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddStaticStyles extends BeforePageDisplay {
	protected function doProcess() {
		// These are the styles used in view mode, for text/background color classes set by
		// the inspectors in VisualEditor - should be loaded on every page load
		$this->out->addModuleStyles( 'ext.bluespice.visualEditorConnector.colors' );
		return true;
	}
}

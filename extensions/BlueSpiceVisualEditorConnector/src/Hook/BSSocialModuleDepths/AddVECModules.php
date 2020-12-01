<?php
namespace BlueSpice\VisualEditorConnector\Hook\BSSocialModuleDepths;

use BlueSpice\Social\Hook\BSSocialModuleDepths;

class AddVECModules extends BSSocialModuleDepths {
	protected function doProcess() {
		if ( $this->getConfig()->get( 'VisualEditorConnectorEnableVisualEditor' ) ) {
			$this->aScripts[] = 'ext.bluespice.visualEditorConnector.standalone.bootstrap';
		}
		return true;
	}
}

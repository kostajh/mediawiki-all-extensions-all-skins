<?php

namespace BlueSpice\InsertLink\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddConfigs extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addJsConfigVars(
			'bsInsertLinkAllowUnMapped',
			$this->getConfig()->get( "InsertLinkAllowUnMapped" )
		);
		$this->out->addJsConfigVars(
			'bsInsertLinkFilesystemMap',
			$this->getConfig()->get( "InsertLinkFilesystemMap" )
		);
		return true;
	}
}

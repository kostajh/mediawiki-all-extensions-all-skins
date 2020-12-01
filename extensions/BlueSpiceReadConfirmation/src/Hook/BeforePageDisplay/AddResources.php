<?php

namespace BlueSpice\ReadConfirmation\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddResources extends BeforePageDisplay {

	protected function doProcess() {
		global $wgNamespacesWithEnabledReadConfirmation;

		$namespaces = isset( $wgNamespacesWithEnabledReadConfirmation )
			? array_keys( $GLOBALS['wgNamespacesWithEnabledReadConfirmation'] )
			: [];

		$this->out->addJsConfigVars(
			'bsgReadConfirmationActivatedNamespaces',
			$namespaces
		);

		$this->out->addModuleStyles( 'ext.readconfirmation.styles' );
		$this->out->addModules( 'ext.readconfirmation.scripts' );
		return true;
	}

}

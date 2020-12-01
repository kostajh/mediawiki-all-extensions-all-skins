<?php

namespace BlueSpice\CategoryCheck\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {

	protected function doProcess() {
		$enabledNamespace = $this->getConfig()->get( 'CategoryCheckNamespaces' );

		if ( $this->ns === null ) {
			return true;
		}

		$currentlyActivated = in_array( $this->ns, $enabledNamespace );

		$explicitlyDeactivated = false;
		if ( isset( $this->definition[ 'categorycheck' ] )
			&& $this->definition[ 'categorycheck' ] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $this->definition[ 'categorycheck' ] )
			&& $this->definition[ 'categorycheck' ] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$this->saveContent .= "\$GLOBALS['bsgCategoryCheckNamespaces'][] = {$this->constName};\n";
		}

		return true;
	}

}

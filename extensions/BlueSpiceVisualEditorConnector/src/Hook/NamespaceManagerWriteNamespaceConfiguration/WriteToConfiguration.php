<?php

namespace BlueSpice\VisualEditorConnector\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {
	protected function skipProcessing() {
		if ( $this->ns === null ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->writeConfiguration( "VisualEditorAvailableNamespaces", "visualeditor" );

		return true;
	}

	/**
	 * Overrides parent method because we need $wg and not $bsg and a different
	 * format
	 *
	 * @param string $configVar - name of the global (bsg) variable
	 * @param string $nsManagerOptionName - name of the option as registered with NSManager
	 */
	protected function writeConfiguration( $configVar, $nsManagerOptionName ) {
		$enabledNamespaces = $this->getConfig()->get( $configVar );

		$currentlyActivated = ( isset( $enabledNamespaces[$this->ns] ) &&
			$enabledNamespaces[$this->ns] === true );

		$explicitlyDeactivated = false;
		if ( isset( $this->definition[$nsManagerOptionName] ) &&
			$this->definition[$nsManagerOptionName] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $this->definition[$nsManagerOptionName] ) &&
			$this->definition[$nsManagerOptionName] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$this->saveContent .= "\$GLOBALS['wg$configVar'][{$this->constName}] = true;\n";
		} else {
			$this->saveContent .= "\$GLOBALS['wg$configVar'][{$this->constName}] = false;\n";
		}
	}
}

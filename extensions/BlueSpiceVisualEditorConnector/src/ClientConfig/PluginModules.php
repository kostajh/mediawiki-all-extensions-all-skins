<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

use BlueSpice\ExtensionAttributeBasedRegistry;

class PluginModules extends ConfigBase {

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorPluginModules'
		);

		$pluginModules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}

		return $pluginModules;
	}
}

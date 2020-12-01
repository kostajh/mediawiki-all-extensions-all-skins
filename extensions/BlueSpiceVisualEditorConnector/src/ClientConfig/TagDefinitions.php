<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

use BlueSpice\ExtensionAttributeBasedRegistry;

class TagDefinitions extends ConfigBase {

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$tagRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorTagDefinitions'
		);
		$tagDefinitions = [];
		foreach ( $tagRegistry->getAllKeys() as $key ) {
			$moduleName = $tagRegistry->getValue( $key );
			$tagDefinitions[] = $moduleName;
		}

		return $tagDefinitions;
	}
}

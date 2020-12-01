<?php

namespace BlueSpice\VisualEditorConnector;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\JSConfigVariable;
use BlueSpice\VisualEditorConnector\ClientConfig\IVisualEditorConnectorClientConfigVariable;
use MediaWiki\Logger\LoggerFactory;

class ClientConfig extends JSConfigVariable {

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorClientConfig'
		);
		$logger = LoggerFactory::getInstance( 'wfDebug' );

		$values = [];
		foreach ( $registry->getAllKeys() as $varName ) {
			$factory = $registry->getValue( $varName );
			if ( !is_callable( $factory ) ) {
				$logger->error(
					'Callback {callback} is not callable', [
						'callback' => $factory
					]
				);
				continue;
			}
			$variableObject = call_user_func( $factory );
			if ( !$variableObject instanceof IVisualEditorConnectorClientConfigVariable ) {
				$logger->error(
					'Handler for {key} expected to be of type {expected}, {actual} given', [
						'key' => $varName,
						'expected' => IVisualEditorConnectorClientConfigVariable::class,
						'actual' => get_class( $variableObject )
					]
				);
				continue;
			}
			$values[$varName] = $variableObject->getValue();
		}

		return $values;
	}
}

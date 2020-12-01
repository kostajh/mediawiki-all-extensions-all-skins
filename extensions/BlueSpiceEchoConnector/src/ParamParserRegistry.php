<?php

namespace BlueSpice\EchoConnector;

class ParamParserRegistry implements \BlueSpice\IRegistry {
	protected $paramParsers;

	public function __construct() {
		$this->paramParsers = \ExtensionRegistry::getInstance()
			->getAttribute( "BlueSpiceEchoConnectorParamParsers" );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getAllKeys() {
		return array_keys( $paramParsers );
	}

	/**
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getValue( $key, $default = '' ) {
		if ( $this->hasKey( $key ) ) {
			return $this->paramParsers[$key];
		}
	}

	/**
	 *
	 * @param string $key
	 * @return bool
	 */
	public function hasKey( $key ) {
		if ( isset( $this->paramParsers[$key] ) ) {
			return true;
		}

		return false;
	}
}

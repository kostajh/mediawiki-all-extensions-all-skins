<?php

namespace BlueSpice\ExtendedStatistics;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BsDiagram;
use Config;

class DiagramFactory {
	/**
	 * ExtensionAttributeBasedRegistry
	 * @var type
	 */
	protected $registry = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var BsDiagram[]
	 */
	protected $diagrams = [];

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $registry
	 * @param Config $config
	 */
	public function __construct( ExtensionAttributeBasedRegistry $registry,
		Config $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 *
	 * @return BsDiagram[]
	 */
	public function getDiagrams() {
		$diagrams = [];
		foreach ( $this->registry->getAllKeys() as $name ) {
			$instance = $this->newFromName( $name );
			if ( !$instance ) {
				continue;
			}
			$diagrams[$name] = $instance;
		}
		return $diagrams;
	}

	/**
	 *
	 * @param string $name
	 * @return BsDiagram
	 */
	public function newFromName( $name ) {
		if ( isset( $this->diagrams[$name] ) ) {
			return $this->diagrams[$name];
		}
		$callback = $this->registry->getValue( $name, null );
		if ( !$callback ) {
			return null;
		}
		if ( !is_callable( $callback ) ) {
			if ( !class_exists( $callback ) ) {
				return null;
			}
			wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
			$instance = new $callback;
			$instance->setConfig( $this->config );
			$this->diagrams[$name] = $instance;
			return $this->diagrams[$name];
		}
		$instance = call_user_func_array( $callback, [
			$this->config
		] );
		$this->diagrams[$name] = $instance;
		return $this->diagrams[$name];
	}

}

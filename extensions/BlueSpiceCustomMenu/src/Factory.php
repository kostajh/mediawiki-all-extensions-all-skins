<?php

namespace BlueSpice\CustomMenu;

use BlueSpice\ExtensionAttributeBasedRegistry;

class Factory {

	/**
	 *
	 * @var ICustomMenu[]
	 */
	protected $instances = [];

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $registry
	 * @param \Config $config
	 */
	public function __construct( $registry, $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $key
	 * @return ICustomMenu|null
	 */
	public function getMenu( $key ) {
		if ( isset( $this->instances[$key] ) ) {
			return $this->instances[$key];
		}
		$callable = $this->registry->getValue( $key, false );
		if ( !$callable ) {
			return null;
		}
		if ( !is_callable( $callable ) ) {
			return null;
		}
		$this->instances[$key] = call_user_func(
			$callable,
			$this->config,
			$key
		);
		return $this->instances[$key];
	}

	/**
	 *
	 * @return ICustomMenu[]
	 */
	public function getAllMenus() {
		foreach ( $this->registry->getAllKeys() as $key ) {
			$this->getMenu( $key );
		}
		return $this->instances;
	}
}

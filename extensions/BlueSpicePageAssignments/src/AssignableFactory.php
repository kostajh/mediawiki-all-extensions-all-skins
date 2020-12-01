<?php

namespace BlueSpice\PageAssignments;

class AssignableFactory {

	/**
	 *
	 * @var BlueSpice\IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param \BlueSpice\IRegistry $registry
	 * @param \Config $config
	 */
	public function __construct( $registry, $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param \IContextSource|null $context
	 * @return IAssignable|null
	 */
	public function factory( $type, \IContextSource $context = null ) {
		if ( !$context ) {
			$context = \RequestContext::getMain();
		}
		$class = $this->registry->getValue(
			$type,
			false
		);
		if ( !$class ) {
			return null;
		}
		return new $class(
			$context,
			$this->config,
			$type
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getRegisteredTypes() {
		return $this->registry->getAllKeys();
	}
}

<?php

namespace BlueSpice\NamespaceCSS\Special;

use BlueSpice\Special\ManagerBase;

class Manager extends ManagerBase {
	/**
	 *
	 * @param string $name
	 * @param string $restriction
	 * @param bool $listed
	 * @param bool $function
	 * @param string $file
	 * @param bool $includable
	 */
	public function __construct( $name = '', $restriction = '', $listed = true, $function = false,
		$file = 'default', $includable = false ) {
		parent::__construct( 'BlueSpiceNamespaceCSSManager', 'editinterface' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-namespacecss-manager';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.namespaceCSS.special'
		];
	}
}

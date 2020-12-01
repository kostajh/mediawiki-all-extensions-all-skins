<?php

class SpecialBlueSpiceExtendedFilelist extends \BlueSpice\SpecialPage {
	public function __construct() {
		parent::__construct( 'BlueSpiceExtendedFilelist' );
	}

	/**
	 *
	 * @param string $par
	 */
	public function execute( $par ) {
		parent::execute( $par );

		$output = $this->getOutput();
		$output->addModules( 'ext.bluespice.extendedFilelist' );
		$output->addModuleStyles( 'ext.bluespice.extendedFilelist.styles' );
		$output->addHTML( '<div id="bs-extendedfilelist-grid"></div>' );
		$this->loadPluginModules( $output, $this->getConfig()->get( 'EFLPluginModules' ) );
	}

	/**
	 *
	 * @param OutputPage $output
	 * @param array $aPluginModules
	 */
	private static function loadPluginModules( $output, $aPluginModules ) {
		foreach ( $aPluginModules as $pluginModule ) {
			$output->addModules( $pluginModule );
		}
	}
}

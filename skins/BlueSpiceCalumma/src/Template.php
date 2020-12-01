<?php

namespace BlueSpice\Calumma;

/**
 * BaseTemplate class for the Chameleon skin
 *
 * @author Stephan Gambke
 * @since 1.0
 * @ingroup Skins
 */
class Template extends \Skins\Chameleon\ChameleonTemplate {

	/**
	 *
	 * @var SkinDataFieldDefinition
	 */
	protected $skinDataFieldDefinition = null;

	/**
	 *
	 * @param \Config|null $config
	 */
	public function __construct( \Config $config = null ) {
		parent::__construct( $config );

		$this->skinDataFieldDefinition =
			new SkinDataFieldDefinition( $this, $this->data );
		$this->skinDataFieldDefinition->init();
	}

	/**
	 * Outputs the entire contents of the page
	 * @return null
	 */
	public function execute() {
		$this->skinDataFieldDefinition->populateDefaultData();
		return parent::execute();
	}

	/**
	 * @inheritDoc
	 */
	public function getFooterLinks( $option = null ) {
		return parent::getFooterLinks( $option );
	}

	/**
	 * @inheritDoc
	 */
	public function getFooterIcons( $option = null ) {
		return parent::getFooterIcons( $option );
	}

}

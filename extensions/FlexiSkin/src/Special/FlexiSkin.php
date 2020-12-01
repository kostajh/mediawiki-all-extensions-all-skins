<?php

namespace MediaWiki\Extension\FlexiSkin\Special;

use Html;
use MediaWiki\Extension\FlexiSkin\IFlexiSkinManager;
use MediaWiki\MediaWikiServices;
use SpecialPage;

class FlexiSkin extends SpecialPage {

	public function __construct() {
		parent::__construct( 'FlexiSkin', 'flexiskin-viewspecialpage', true );
	}

	/**
	 *
	 * @param string $par
	 * @return void
	 */
	public function execute( $par ) {
		parent::execute( $par );

		$this->setHeaders();
		$this->getOutput()->enableOOUI();
		$this->getOutput()->addModuleStyles( 'ext.flexiskin.specialpage.styles' );
		$this->getOutput()->addModules( 'ext.flexiskin.specialpage.scripts' );
		$this->getOutput()->addHTML( Html::element( 'div', [ 'id' => 'fs-container' ] ) );

		/** @var IFlexiSkinManager $manager */
		$manager = MediaWikiServices::getInstance()->getService( 'FlexiSkinManager' );
		$flexiSkin = $manager->getFlexiSkin();
		if ( $flexiSkin === null ) {
			$flexiSkin = $manager->create( 'default', [] );
		}
		$config = $flexiSkin->getConfig();

		foreach ( $manager->getPlugins() as $key => $plugin ) {
			$plugin->adaptConfiguration( $config );
			$plugin->setDefaults( $flexiSkin, $config );
		}
		$adaptedSkin = new \MediaWiki\Extension\FlexiSkin\FlexiSkin(
			$flexiSkin->getId(),
			$flexiSkin->getName(),
			$config,
			$flexiSkin->isActive()
		);

		$this->getOutput()->addJsConfigVars( 'wgFlexiSkin', $adaptedSkin );
		$this->getOutput()->addJsConfigVars(
			'wgFlexiSkinColorPresets', $this->getConfig()->get( 'FlexiSkinColorPresets' )
		);
		$this->getOutput()->addHTML( Html::element( 'span', [
			'id' => 'fs-sp-loading'
		], \Message::newFromKey( 'flexiskin-special-loading-ph' )->text() ) );
	}

	/**
	 * @return string groupname
	 */
	protected function getGroupName() {
		return 'other';
	}
}

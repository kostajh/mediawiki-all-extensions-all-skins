<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\Social\ResourceCollector;
use MediaWiki\MediaWikiServices;

class AddResources extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.social.icon' );

		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}

		try{
			$oCollector = ResourceCollector::getMain();
		} catch ( \Exception $ex ) {
			return true;
		}

		if ( !empty( $oCollector->getModuleStyles() ) ) {
			$this->out->addModuleStyles( $oCollector->getModuleStyles() );
		}

		$this->out->addModules( 'ext.bluespice.social' );
		$this->out->addModules( 'ext.bluespice.social.timeline' );
		$this->out->addModuleStyles( 'ext.bluespice.social.timeline.styles' );
		$this->addLegacyResources();
		// isLoaded is not working correctly
		// also this modules can not be loaded within JS because stuff breaks
		$extensions = \ExtensionRegistry::getInstance()->getAllThings();
		if ( isset( $extensions[ 'MultimediaViewer' ] ) ) {
			$this->out->addModules(
				[ 'mmv.head', 'mmv.bootstrap.autostart' ]
			);
		}

		$this->out->addJsConfigVars(
			'bsgSocialVarMessageKeys',
			$oCollector->getVarMessageKeys()
		);
		return true;
	}

	/**
	 * DEPRECATED
	 * @deprecated since version 3.2
	 */
	private function addLegacyResources() {
		if ( empty( $GLOBALS['wgHooks']['BSSocialModuleDepths'] ) ) {
			return;
		}
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$aConfig = $aScripts = $aStyles = $aVarMsgKeys = [];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSSocialModuleDepths', [
			// deprecated
			$this->getContext()->getOutput(),
			// deprecated
			$this->getContext()->getSkin(),
			&$aConfig,
			&$aScripts,
			&$aStyles,
			// deprecated
			&$aVarMsgKeys,
		] );
		if ( !empty( $aConfig ) ) {
			$this->out->addJsConfigVars( $aConfig );
		}
		if ( !empty( $aScripts ) ) {
			$this->out->addJsConfigVars( 'bsgSocialLegacyModules', $aScripts );
		}
	}

}

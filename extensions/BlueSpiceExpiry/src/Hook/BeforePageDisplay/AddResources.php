<?php

namespace BlueSpice\Expiry\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.Expiry' );
		$this->out->addModules( 'ext.bluespice.expiry.pageinfo.flyout' );
		$this->out->addModuleStyles( 'ext.bluespice.Expiry.Highlight' );

		$title = $this->getContext()->getTitle();
		$canExpire = false;
		$canDeleteExpiration = false;
		if ( $title && $title->exists() ) {
			$canExpire = \MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'expirearticle', $this->getContext()->getUser(), $title );
			$canDeleteExpiration = \MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'expiry-delete', $this->getContext()->getUser(), $title );
		}

		$this->out->addJsConfigVars( 'bsgExpiryCanExpire', $canExpire );
		$this->out->addJsConfigVars( 'bsgExpiryCanDeleteExpiration', $canDeleteExpiration );
	}

}

<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use ApiBase;
use ApiMain;
use MediaWiki\Extension\FlexiSkin\IFlexiSkinManager;
use MediaWiki\MediaWikiServices;

abstract class FlexiSkinApiBase extends ApiBase {
	/** @var IFlexiSkinManager */
	protected $flexiSkinManager = null;

	/**
	 * @param ApiMain $mainModule
	 * @param string $moduleName Name of this module
	 * @param string $modulePrefix Prefix to use for parameter names
	 */
	public function __construct( ApiMain $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );
		$this->flexiSkinManager = MediaWikiServices::getInstance()->get( 'FlexiSkinManager' );
	}

	protected function checkPermissions() {
		$user = $this->getUser();
		$userHasRight = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$user,
			'flexiskin-api'
		);

		if ( !$userHasRight ) {
			$this->dieWithError( 'permissiondenied', 'permissiondenied' );
		}
	}
}

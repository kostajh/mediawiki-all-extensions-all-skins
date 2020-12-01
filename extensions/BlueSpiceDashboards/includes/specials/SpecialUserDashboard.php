<?php

class SpecialUserDashboard extends SpecialDashboard {

	public function __construct() {
		parent::__construct( 'UserDashboard', 'dashboards-viewspecialpage-userdashboard' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getConds(): array {
		return [ 'dc_identifier' => $this->getUser()->getId() ];
	}

	/**
	 * @inheritDoc
	 */
	protected function getSaveBackend(): string {
		return 'saveUserDashboardConfig';
	}

	/**
	 * @inheritDoc
	 */
	protected function getLocation(): string {
		return 'UserDashboard';
	}

	/**
	 * @inheritDoc
	 */
	protected function getContainerId(): string {
		return 'bs-dashboards-userdashboard';
	}

	/**
	 * @inheritDoc
	 */
	protected function addModules( OutputPage $out ) {
		$out->addModuleStyles( 'ext.bluespice.extjs.BS.portal.css' );
		$out->addModules( 'ext.bluespice.dashboards.userDashboard' );
		$out->addModuleStyles( 'ext.bluespice.dashboards.styles' );
	}
}

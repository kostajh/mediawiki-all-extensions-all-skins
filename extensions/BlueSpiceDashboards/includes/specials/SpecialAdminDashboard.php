<?php

class SpecialAdminDashboard extends SpecialDashboard {
	public function __construct() {
		parent::__construct( 'AdminDashboard', 'wikiadmin' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getConds(): array {
		return [ 'dc_type' => 'admin' ];
	}

	/**
	 * @inheritDoc
	 */
	protected function getSaveBackend(): string {
		return 'saveAdminDashboardConfig';
	}

	/**
	 * @inheritDoc
	 */
	protected function getLocation(): string {
		return 'AdminDashboard';
	}

	/**
	 * @inheritDoc
	 */
	protected function getContainerId(): string {
		return 'bs-dashboards-admindashboard';
	}

	/**
	 * @inheritDoc
	 */
	protected function addModules( OutputPage $out ) {
		$out->addModuleStyles( 'ext.bluespice.extjs.BS.portal.css' );
		$out->addModuleStyles( 'ext.bluespice.dashboards.styles' );
		$out->addModules( 'ext.bluespice.dashboards.adminDashboard' );
	}
}

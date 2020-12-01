<?php

namespace BlueSpice\Dashboards\Hook;

use BlueSpice\Hook;

abstract class BSDashboardsUserDashboardPortalPortlets extends Hook {

	/**
	 *
	 * @var array
	 */
	protected $portlets;

	/**
	 * Fired in SpecialUserDashboard::execute
	 * Fired in maintenance/clearConfigs.php
	 *
	 * @param array &$portlets reference to array portlets
	 * @return bool
	 */
	public static function callback( &$portlets ) {
		$className = static::class;
		$hookHandler = new $className(
			null, null, $portlets
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param array &$portlets reference to array portlets
	 */
	public function __construct( $context, $config, &$portlets ) {
		parent::__construct( $context, $config );
		$this->portlets = &$portlets;
	}

}

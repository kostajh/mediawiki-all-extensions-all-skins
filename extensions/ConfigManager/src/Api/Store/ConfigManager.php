<?php

namespace ConfigManager\Api\Store;

use BlueSpice\Context;
use BlueSpice\Services;
use ConfigManager\Data\ConfigManager\Store;

class ConfigManager extends \BlueSpice\Api\Store {

	/**
	 *
	 * @return string[]
	 */
	protected function getRequiredPermissions() {
		return [ 'configmanager-viewspecialpage' ];
	}

	/**
	 *
	 * @return Store
	 */
	protected function makeDataStore() {
		$services = Services::getInstance();
		return new Store(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			$services->getDBLoadBalancer(),
			$services->getService( 'BSConfigDefinitionFactory' )
		);
	}
}

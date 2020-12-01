<?php
/*
 * Copyright (C) 2018
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

namespace MediaWiki\Extension\LDAPProvider;

use ExtensionRegistry;
use HashConfig;
use MediaWiki\MediaWikiServices;
use MultiConfig;
use MWException;

class DomainConfigFactory {

	private const DEFAULT_CONF_ATTR = 'LDAPProviderDefaultSettings';

	/**
	 *
	 * @var DomainConfigFactory
	 */
	protected static $instance = null;

	/**
	 *
	 * @var array
	 */
	protected $config = null;

	/**
	 * @param IDomainConfigProvider $configProvider to config file
	 */
	public function __construct( $configProvider ) {
		$this->config = $configProvider->getConfigArray();
	}

	/**
	 * Returns a specific configuration from the common LDAP configuration file
	 * referenced in "$LDAPProviderDomainConfigs"
	 * @param string $domain to use
	 * @param string $section to get
	 * @return Config
	 */
	public function factory( $domain, $section ) {
		if ( !isset( $this->config[$domain] ) ) {
			throw new LDAPNoDomainConfigException(
				"No configuration available for domain '$domain'!"
			);
		}
		if ( !isset( $this->config[$domain][$section] ) ) {
			throw new MWException(
				"No section '$section' found in configuration for "
				. "domain '$domain'!"
			);
		}

		$extRegistry = ExtensionRegistry::getInstance();
		$defaultConfig
			= $extRegistry->getAttribute( static::DEFAULT_CONF_ATTR );
		$defaultSectionConf = [];
		if ( isset( $defaultConfig[$section] ) ) {
			$defaultSectionConf = $defaultConfig[$section];
		}

		return new MultiConfig( [
			new HashConfig( $this->config[$domain][$section] ),
			new HashConfig( $defaultSectionConf )
		] );
	}

	/**
	 * Returns all configured domains
	 * @return string[]
	 */
	public function getConfiguredDomains() {
		return array_keys( $this->config );
	}

	/**
	 * Accessor for the singleton object
	 * @return DomainConfigFactory
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			$extensionConfig = MediaWikiServices::getInstance()
				->getConfigFactory()->makeConfig( 'ldapprovider' );

			$callback = $extensionConfig->get( Config::DOMAIN_CONFIG_PROVIDER );
			$configProvider = call_user_func_array(
				$callback,
				[ $extensionConfig ]
			);

			self::$instance = new self( $configProvider );
		}
		return self::$instance;
	}
}

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

namespace MediaWiki\Extension\LDAPProvider\DomainConfigProvider;

use FormatJson;
use MediaWiki\Extension\LDAPProvider\Config;
use MediaWiki\Extension\LDAPProvider\IDomainConfigProvider;
use MWException;

class LocalJSONFile implements IDomainConfigProvider {

	/**
	 *
	 * @var array
	 */
	private $configArray = [];

	/**
	 *
	 * @param string $jsonFilePath The absolute path to the JSON file
	 */
	public function __construct( $jsonFilePath ) {
		if ( !is_readable( $jsonFilePath ) ) {
			throw new MWException(
				wfMessage( 'ldapprovider-domain-config-not-found' )->params( $jsonFilePath )->plain()
			);
		}

		$this->configArray = FormatJson::decode(
			file_get_contents( $jsonFilePath ),
			true
		);

		if ( $this->configArray === false
			|| count( $this->configArray ) === 0 ) {
			throw new MWException(
				"Could not parse configuration file '$jsonFilePath'!"
			);
		}
	}

	/**
	 * @return array
	 */
	public function getConfigArray() {
		return $this->configArray;
	}

	/**
	 *
	 * @param Config $ldapConfig The config to be used
	 * @return LocalJSONFile
	 */
	public static function newInstance( $ldapConfig ) {
		return new self( $ldapConfig->get( Config::DOMAIN_CONFIGS ) );
	}
}

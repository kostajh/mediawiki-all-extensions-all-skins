<?php

namespace MediaWiki\Extension\LDAPProvider;

use Config;

class Serverlist {
	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param Config $config to get server from
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Logic taken from "Extension:LdapAuthentication",
	 * LdapAuthenticationPlugin::connect
	 * @return string
	 */
	public function __toString() {
		switch ( $this->getEncType() ) {
			case EncType::LDAPI:
				$serverpre = "ldapi://";
				break;
			case EncType::SSL:
				$serverpre = "ldaps://";
				break;
			default:
				$serverpre = "ldap://";
		}

		// Make a space separated list of server strings with the connection type
		// string added.
		$servers = "";
		$tmpservers = $this->config->get( ClientConfig::SERVER );
		$tok = strtok( $tmpservers, " " );
		while ( $tok ) {
			$servers = $servers . " " . $serverpre . $tok . ":" . $this->getPort();
			$tok = strtok( " " );
		}
		$servers = trim( $servers );

		return $servers;
	}

	/**
	 * Get the right port #
	 * @return string
	 */
	protected function getPort() {
		$port = '389';
		if ( $this->isSSL() ) {
			$port = '636';
		}
		if ( $this->config->has( ClientConfig::PORT ) ) {
			$port = $this->config->get( ClientConfig::PORT );
		}
		return $port;
	}

	/**
	 * Determine if this is using SSL or no
	 * @return bool
	 */
	protected function isSSL() {
		return $this->config->has( ClientConfig::ENC_TYPE )
			&& $this->config->get( ClientConfig::ENC_TYPE ) == EncType::SSL;
	}

	/**
	 * Get the configured encoding type
	 * @return string
	 */
	protected function getEncType() {
		if ( $this->config->has( ClientConfig::ENC_TYPE ) ) {
			return $this->config->get( ClientConfig::ENC_TYPE );
		}
		return '';
	}

}

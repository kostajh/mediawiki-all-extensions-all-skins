<?php

namespace MediaWiki\Extension\LDAPProvider;

use Config;
use MWException;

abstract class UserGroupsRequest {

	/**
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var string
	 */
	protected $groupBaseDN = '';

	/**
	 * @param Client $ldapClient to use
	 * @param Config $config will be delivered here
	 */
	public function __construct( $ldapClient, Config $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->groupBaseDN = $config->get( ClientConfig::GROUP_BASE_DN );
	}

	/**
	 *
	 * @param Client $ldapClient The client to be used
	 * @param Config $config The config to be used
	 * @return UserGroupsRequest
	 * @throws MWException
	 */
	public static function factory( $ldapClient, Config $config ) {
		$request = new static( $ldapClient, $config );
		return $request;
	}

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	abstract public function getUserGroups( $username );
}

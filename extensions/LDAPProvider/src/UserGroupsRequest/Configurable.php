<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;
use MWException;

class Configurable extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userDN = new EscapedString( $this->ldapClient->getUserDN( $username ) );
		$baseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );
		$dn = 'dn';

		if ( $baseDN === '' ) {
			$baseDN = null;
		}

		$ret = [];
		$objectClass = $this->config->get( ClientConfig::GROUP_OBJECT_CLASS );
		if ( empty( trim( $objectClass ) ) ) {
			throw new MWException( sprintf(
				"Parameter %s must be set when configurable groups request is used",
				ClientConfig::GROUP_OBJECT_CLASS
			) );
		}
		$groupAttribute = $this->config->get( ClientConfig::GROUP_ATTRIBUTE );

		$groups = $this->ldapClient->search(
			"(&(objectclass=$objectClass)($groupAttribute=$userDN))",
			$baseDN, [ $dn ]
		);

		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}
		return new GroupList( $ret );
	}

}

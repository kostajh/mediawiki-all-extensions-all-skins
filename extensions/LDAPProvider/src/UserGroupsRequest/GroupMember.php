<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class GroupMember extends UserGroupsRequest {

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
		if ( $this->config->get( ClientConfig::NESTED_GROUPS ) ) {
			$groups = $this->ldapClient->search(
				"(member:1.2.840.113556.1.4.1941:=$userDN)",
				$baseDN, [ $dn ]
			);
		} else {
			$groups = $this->ldapClient->search(
				"(&(objectclass=group)(member=$userDN))",
				$baseDN, [ $dn ]
			);
		}
		$ret = [];
		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}
		return new GroupList( $ret );
	}

}

<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class UserGidNumber extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userInfo = $this->ldapClient->getUserInfo( $username );

		if ( !isset( $userInfo['gidnumber'] ) ) {
			return new GroupList( [] );
		}

		$gid = $userInfo['gidnumber'];
		$dn = 'dn';
		$groupBaseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );

		$groups = $this->ldapClient->search(
			"(&(objectclass=posixGroup)(gidnumber=$gid))",
			$this->$groupBaseDN, [ $dn ]
		);
		$ret = [];
		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}
		return new GroupList( $ret );
	}
}

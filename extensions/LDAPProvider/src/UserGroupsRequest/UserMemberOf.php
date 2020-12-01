<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class UserMemberOf extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$res = $this->ldapClient->getUserInfo( $username );

		$groupDNs = $res['memberof'];
		/**
		 * With some LDAP backends this field may be just a string, if only one
		 * group is assigned
		 */
		if ( !is_array( $groupDNs ) ) {
			$groupDNs = [ $groupDNs ];
		}

		return new GroupList( $groupDNs );
	}

}

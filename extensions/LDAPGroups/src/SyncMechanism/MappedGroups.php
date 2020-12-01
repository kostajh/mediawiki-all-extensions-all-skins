<?php

namespace MediaWiki\Extension\LDAPGroups\SyncMechanism;

use MediaWiki\Extension\LDAPGroups\Config;

class MappedGroups extends Base {

	/**
	 *
	 * @var array
	 */
	private $map;

	/**
	 *
	 */
	protected function doSync() {
		$this->map = $this->config->get( Config::MAPPING );

		$currentLDAPGroups = $this->filterNonLDAPGroups( $this->user->getGroups() );
		$ldapGroupMembership = $this->mapGroupsFromLDAP();

		$groupsToAdd = array_diff( $ldapGroupMembership, $currentLDAPGroups );
		foreach ( $groupsToAdd as $groupToAdd ) {
			$this->addGroup( $groupToAdd );
		}

		$groupsToRemove = array_diff( $currentLDAPGroups, $ldapGroupMembership );
		foreach ( $groupsToRemove as $groupToRemove ) {
			$this->removeGroup( $groupToRemove );
		}
	}

	/**
	 * Given a list of groups return those that are managed in LDAP
	 *
	 * @param array $groups MediaWiki Groups
	 * @return array
	 */
	private function filterNonLDAPGroups( array $groups ) {
		$ret = [];
		foreach ( $groups as $group ) {
			if ( isset( $this->map[$group] ) ) {
				$ret[] = $group;
			}
		}
		return $ret;
	}

	private function mapGroupsFromLDAP() {
		$allLDAPGroups = array_map( 'strtolower', $this->groupList->getFullDNs() );
		$dnToWikiMap = [];

		foreach ( $this->map as $localGroup => $fullDNs ) {
			if ( !is_array( $fullDNs ) ) {
				$fullDNs = [ $fullDNs ];
			}
			foreach ( $fullDNs as $fullDN ) {
				$normalizedFullDN = strtolower( $fullDN );
				if ( !isset( $dnToWikiMap[$normalizedFullDN] ) ) {
					$dnToWikiMap[$normalizedFullDN] = [];
				}
				$dnToWikiMap[$normalizedFullDN] = array_merge(
					$dnToWikiMap[$normalizedFullDN],
					[ $localGroup ]
				);
			}
		}

		$ret = [];
		foreach ( $allLDAPGroups as $dn ) {
			if ( isset( $dnToWikiMap[$dn] ) ) {
				$ret = array_merge( $ret, $dnToWikiMap[$dn] );
			}
		}
		return $ret;
	}
}

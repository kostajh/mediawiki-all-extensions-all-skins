<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use MediaWiki\Extension\LDAPProvider\GroupList;

class GroupListTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\GroupList::getShortNames
	 */
	public function testGetShortNames() {
		$groupList = new GroupList( [
			'CN=WikiReader,OU=usergroups,OU=Groups,DC=company,DC=local',
			'CN=Ninja,OU=SpecialGroups,DC=company,DC=local'
		] );

		$this->assertEquals(
			[ 'wikireader', 'ninja' ], $groupList->getShortNames()
		);
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\GroupList::getFullDNs
	 */
	public function testGetFullDNs() {
		$fullDNs = [
			'CN=WikiReader,OU=usergroups,OU=Groups,DC=company,DC=local',
			'CN=Ninja,OU=SpecialGroups,DC=company,DC=local'
		];
		$groupList = new GroupList( $fullDNs );

		$this->assertEquals( $fullDNs, $groupList->getFullDNs() );
	}
}

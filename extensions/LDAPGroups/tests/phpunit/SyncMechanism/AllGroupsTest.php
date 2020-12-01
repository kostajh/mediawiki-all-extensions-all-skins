<?php

namespace MediaWiki\Extension\LDAPGroups\Tests\SyncMechanism;

use HashConfig;
use MediaWiki\Extension\LDAPGroups\SyncMechanism\AllGroups;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWikiTestCase;
use Psr\Log\NullLogger;
use TestUserRegistry;

class AllGroupsTest extends MediaWikiTestCase {

	/**
	 * @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\AllGroups::factory
	 */
	public function testFactory() {
		$domainConfig = new \HashConfig( [] );
		$logger = new NullLogger;
		$syncMechanism = AllGroups::factory( $domainConfig, $logger );

		$this->assertInstanceOf(
			\MediaWiki\Extension\LDAPGroups\ISyncMechanism::class,
			$syncMechanism
		);
	}

	/**
	 *
	 * @param string[] $locallyAvailableGroups
	 * @param string[] $locallyManagedGroupsm
	 * @param string[] $initialGroups
	 * @param string[] $fullDNs
	 * @param string[] $expectedGroups
	 * @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\AllGroups::sync
	 * @dataProvider provideTestSyncData
	 */
	public function testSync( $locallyAvailableGroups, $locallyManagedGroups, $initialGroups,
		$fullDNs, $expectedGroups ) {
		$testUser = TestUserRegistry::getMutableTestUser( 'AllGroupsTestUser', $initialGroups );
		$user = $testUser->getUser();
		$groupList = new GroupList( $fullDNs );
		$config = new HashConfig( [
			'locally-managed' => $locallyManagedGroups
		] );
		$logger = new NullLogger;

		$syncMechanism = new AllGroups( $logger, $locallyAvailableGroups );
		$syncMechanism->sync( $user, $groupList, $config );

		$actualGroups = $user->getGroups();

		sort( $actualGroups );
		sort( $expectedGroups );

		$this->assertArrayEquals(
			$expectedGroups,
			$actualGroups,
			'Groups have not been set properly!'
		);
	}

	public function provideTestSyncData() {
		$locallyAvailableGroups = [ 'bot', 'sysop', 'bureaucrat', 'Group_From_LDAP_1',
			'Group_From_LDAP_2', 'Local_Group'
		];
		$locallyManagedGroups = [ 'Local_Group' ];
		$initialGroups = [ 'sysop', 'Group_From_LDAP_1' ];

		return [
			'keep-implicit-locally-managed' => [
				$locallyAvailableGroups,
				$locallyManagedGroups,
				$initialGroups,
				[],
				[
					'sysop'
				]
			],
			'keep-implicit-locally-managed-and-add-ldap-managed' => [
				$locallyAvailableGroups,
				$locallyManagedGroups,
				$initialGroups,
				[
					'cn=group_from_ldap_2,ou=groups,dc=LDAP,dc=example,dc=com',
					'cn=group_from_ldap_3,ou=groups,dc=LDAP,dc=example,dc=com',
				],
				[
					'sysop',
					'Group_From_LDAP_2'
				]
			],
			'add-ldap-managed' => [
				$locallyAvailableGroups,
				$locallyManagedGroups,
				$initialGroups,
				[
					'cn=group_from_ldap_1,ou=groups,dc=LDAP,dc=example,dc=com',
					'cn=group_from_ldap_2,ou=groups,dc=LDAP,dc=example,dc=com',
				],
				[
					'sysop',
					'Group_From_LDAP_1',
					'Group_From_LDAP_2'
				]
			],
			'remove-ldap-managed' => [
				$locallyAvailableGroups,
				$locallyManagedGroups,
				[ 'Group_From_LDAP_1', 'Group_From_LDAP_2' ],
				[
					'cn=group_from_ldap_2,ou=groups,dc=LDAP,dc=example,dc=com'
				],
				[
					'Group_From_LDAP_2'
				]
			]
		];
	}
}

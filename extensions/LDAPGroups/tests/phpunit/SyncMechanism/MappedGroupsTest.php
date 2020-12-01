<?php

namespace MediaWiki\Extension\LDAPGroups\Tests\SyncMechanism;

use HashConfig;
use MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWikiTestCase;
use Psr\Log\NullLogger;
use TestUserRegistry;

class MappedGroupsTest extends MediaWikiTestCase {

	/**
	 * @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups::factory
	 */
	public function testFactory() {
		$domainConfig = new \HashConfig( [] );
		$logger = new NullLogger;
		$syncMechanism = MappedGroups::factory( $domainConfig, $logger );

		$this->assertInstanceOf(
			\MediaWiki\Extension\LDAPGroups\ISyncMechanism::class,
			$syncMechanism
		);
	}

	/**
	 *
	 * @param string[] $mapping
	 * @param string[] $initialGroups
	 * @param string[] $fullDNs
	 * @param string[] $expectedGroups
	 * @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups::sync
	 * @dataProvider provideTestSyncData
	 */
	public function testSync( $mapping, $initialGroups, $fullDNs, $expectedGroups ) {
		$testUser = TestUserRegistry::getMutableTestUser( 'MappedGroupsTestUser', $initialGroups );
		$user = $testUser->getUser();
		$groupList = new GroupList( $fullDNs );
		$config = new HashConfig( [
			'mapping' => $mapping
		] );
		$logger = new NullLogger;

		$syncMechanism = new MappedGroups( $logger );
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
		return [
				// https://www.mediawiki.org/w/index.php?title=Extension:LdapGroups&oldid=2595259#Group_mapping
				'set-from-ldap-and-remove-local-1' => [
					[
						'AWSUsers' => [
							'nc=aws-production,ou=security group,o=top'
						],
						'NavAndGuidance' => [
							'cn=g001,OU=Groups,o=top',
							'cn=g002,OU=Groups,o=top',
							'cn=g003,OU=Groups,o=top',
						]
					],
				[
					'sysop',
					'some_group',
					'NavAndGuidance'
				],
				[
					'nc=aws-production,ou=security group,o=top'
				],
				[
					'sysop',
					'some_group',
					'AWSUsers'
				]
			],
			'set-from-ldap-and-remove-local-2' => [
				[
					'mathematicians' => 'ou=mathematicians,dc=example,dc=com',
					'scientists' => 'ou=scientists,dc=example,dc=com'
				],
				[
					'sysop',
					'some_group',
					'mathematicians'
				],
				[
					'OU=SCIENTISTS,DC=EXAMPLE,DC=COM'
				],
				[
					'sysop',
					'some_group',
					'scientists'
				]
			],
			'Topic:V3s73k1q4736ov68#1' => [
				[
					"sysop" => "cn=wiki,cn=groups,dc=xx,dc=xxx"
				],
				[],
				[
					"cn=wiki,cn=groups,dc=xx,dc=xxx"
				],
				[
					'sysop'
				]
			],
			'Topic:Vn9jblaqr69fim14#1' => [
				[
					"sysop" => "CN=Wiki_Admin,OU=Groups,DC=mydomain,DC=net",
					"wiki-read" => "CN=Wiki_ReadOnly,OU=Groups,DC=mydomain,DC=net",
					"wiki-write" => "CN=Wiki_ReadWrite,OU=Groups,DC=mydomain,DC=net"
				],
				[
					'wiki-read',
					'wiki-write'
				],
				[
					"CN=Wiki_ReadOnly,OU=Groups,DC=mydomain,DC=net"
				],
				[
					'wiki-read'
				]
			]
		];
	}
}

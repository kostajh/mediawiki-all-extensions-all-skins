<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use HashConfig;
use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EncType;
use MediaWiki\Extension\LDAPProvider\Serverlist;

class ServerlistTest extends \PHPUnit\Framework\TestCase {

	/**
	 *
	 * @param \Config $config
	 * @param string $expected
	 *
	 * @covers MediaWiki\Extension\LDAPProvider\Serverlist::__toString
	 * @dataProvider provideConfigs
	 */
	public function testToString( $config, $expected ) {
		$config = new HashConfig( $config );
		$serverlist = new Serverlist( $config );

		$this->assertEquals( $expected, (string)$serverlist );
	}

	public function provideConfigs() {
		return [
			'only-one-server' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld'
				],
				'ldap://ldap.company.tld:389'
			],
			'one-server-and-ssl' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL
				],
				'ldaps://ldap.company.tld:636'
			],
			'two-servers-and-ssl' => [
				[
					ClientConfig::SERVER
					=> 'ldap1.company.tld ldap2.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL
				],
				'ldaps://ldap1.company.tld:636 ldaps://ldap2.company.tld:636'
			],
			'two-servers-and-ldapi' => [
				[
					ClientConfig::SERVER
					=> 'ldap1.company.tld ldap2.company.tld',
					ClientConfig::ENC_TYPE => EncType::LDAPI
				],
				'ldapi://ldap1.company.tld:389 ldapi://ldap2.company.tld:389'
			],
			'one-server-and-ssl-with-non-standard-port' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL,
					ClientConfig::PORT => '12345'
				],
				'ldaps://ldap.company.tld:12345'
			],
		];
	}
}

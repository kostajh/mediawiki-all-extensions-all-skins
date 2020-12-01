<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use ConfigException;
use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\DomainConfigFactory;
use MediaWiki\Extension\LDAPProvider\DomainConfigProvider\LocalJSONFile;

class DomainConfigFactoryTest extends \MediaWikiTestCase {

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\DomainConfigFactory::factory
	 */
	public function testDefaultConfig() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', ClientConfig::DOMAINCONFIG_SECTION );

		$this->assertEquals( 'clear', $config->get( ClientConfig::ENC_TYPE ) );
		$this->assertEquals(
			'someDN', $config->get( ClientConfig::USER_BASE_DN )
		);
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\DomainConfigFactory::factory
	 */
	public function testArbitrarySection() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', 'some-arbitrary-section' );

		$this->assertEquals( 42, $config->get( 'conf1' ) );
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @covers MediaWiki\Extension\LDAPProvider\DomainConfigFactory::factory
	 */
	public function testExceptionOnMissingDefault() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', 'some-arbitrary-section' );
		$this->expectException( ConfigException::class );
		$configWithNoDefault = $config->get( 'conf2' );
	}

	/**
	 *
	 * @return DomainConfigFactory
	 */
	protected function makeDomainConfigFactory() {
		return new DomainConfigFactory(
			new LocalJSONFile( __DIR__ . '/data/testconfig.json' )
		);
	}

}

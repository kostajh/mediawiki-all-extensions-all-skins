<?php

namespace MediaWiki\Extension\LDAPProvider;

use GlobalVarConfig;

class Config extends GlobalVarConfig {

	public const CLIENT_REGISTRY = 'ClientRegistry';
	public const DOMAIN_CONFIGS = 'DomainConfigs';
	public const CACHE_TYPE = 'CacheType';
	public const CACHE_TIME = 'CacheTime';
	public const DOMAIN_CONFIG_PROVIDER = 'DomainConfigProvider';
	public const DEFAULT_DOMAIN = 'DefaultDomain';
	public const PRE_SEARCH_USERNAME_MODIFIER_REGISTRY = 'PreSearchUsernameModifierRegistry';

	public function __construct() {
		parent::__construct( 'LDAPProvider' );
	}

	/**
	 * Factory method for MediaWikiServices
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}
}

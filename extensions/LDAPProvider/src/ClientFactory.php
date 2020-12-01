<?php

namespace MediaWiki\Extension\LDAPProvider;

use MWException;

class ClientFactory {

	/**
	 *
	 * @var Client[]
	 */
	protected $clients = [];

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @var callable[]
	 */
	protected $domainClientFactories = [];

	protected function __construct() {
		$this->config = Config::newInstance();
		$this->domainClientFactories = $this->config->get( "ClientRegistry" );
	}

	/**
	 *
	 * @var ClientFactory
	 */
	protected static $instance = null;

	/**
	 * Accessor for the singleton object
	 * @return ClientFactory
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Given a domain, get a client
	 *
	 * @param string $domain to get
	 * @return Client
	 * @throws MWException
	 */
	public function getForDomain( $domain ) {
		if ( !isset( $this->clients[$domain] ) ) {
			if ( !isset( $this->domainClientFactories[$domain] ) ) {
				$clientConfig = DomainConfigFactory::getInstance()->factory(
					$domain,
					ClientConfig::DOMAINCONFIG_SECTION
				);
				$preSearchUsernameModifierProcessor = new PreSearchUsernameModifierProcessor(
					$this->config->get( Config::PRE_SEARCH_USERNAME_MODIFIER_REGISTRY )
				);
				$this->clients[$domain] = new Client(
					$clientConfig, $preSearchUsernameModifierProcessor
				);
			} else {
				$callback = $this->domainClientFactories[$domain];
				$this->clients[$domain] = $callback();
			}
			if ( $this->clients[$domain] instanceof Client === false ) {
				throw new MWException(
					"Client factory for domain '$domain' did not "
					. "return a valid Client object"
				);
			}
		}
		return $this->clients[$domain];
	}
}

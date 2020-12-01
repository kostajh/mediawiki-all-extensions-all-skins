<?php

namespace MediaWiki\Extension\LDAPProvider;

use MediaWiki\MediaWikiServices;
use User;
use Wikimedia\Rdbms\LoadBalancer;

class UserDomainStore {

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadbalancer = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param LoadBalancer $loadbalancer to use
	 * @param Config|null $config
	 */
	public function __construct( LoadBalancer $loadbalancer, $config = null ) {
		$this->loadbalancer = $loadbalancer;
		$this->config = $config;
		if ( $this->config === null ) {
			$this->config = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'ldapprovider' );
		}
	}

	/**
	 * @param User $user to get domain for
	 * @return string|null
	 */
	public function getDomainForUser( User $user ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbr = $this->loadbalancer->getConnection( DB_REPLICA );
			$row = $dbr->selectRow(
				'ldap_domains',
				[ 'domain' ],
				[ 'user_id' => $userId ],
				__METHOD__ );

			if ( $row ) {
				return $row->domain;
			}
		}

		$defaultDomain = $this->config->get( Config::DEFAULT_DOMAIN );
		if ( !empty( $defaultDomain ) ) {
			return $defaultDomain;
		}

		return null;
	}

	/**
	 * @param User $user to set
	 * @param string $domain to set user to
	 * @return bool
	 */
	public function setDomainForUser( $user, $domain ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbw = $this->loadbalancer->getConnection( DB_MASTER );
			$olddomain = $this->getDomainForUser( $user );
			if ( $olddomain ) {
				return $dbw->update(
					'ldap_domains',
					[ 'domain' => $domain ],
					[ 'user_id' => $userId ],
					__METHOD__
				);
			} else {
				return $dbw->insert(
					'ldap_domains',
					[
						'domain' => $domain,
						'user_id' => $userId
					],
					__METHOD__
				);
			}
		}
		return false;
	}
}

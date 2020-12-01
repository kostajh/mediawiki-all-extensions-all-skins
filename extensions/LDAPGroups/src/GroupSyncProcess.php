<?php

namespace MediaWiki\Extension\LDAPGroups;

use Exception;
use MediaWiki\Extension\LDAPProvider\Client;
use MediaWiki\Logger\LoggerFactory;
use MWException;
use Status;

class GroupSyncProcess {

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @var \Config
	 */
	protected $domainConfig = null;

	/**
	 *
	 * @var Client
	 */
	protected $client = null;

	/**
	 *
	 * @var array
	 */
	protected $callbackRegistry = [];

	/**
	 *
	 * @var Status
	 */
	protected $status = null;

	/**
	 *
	 * @param \User $user
	 * @param \Config $domainConfig
	 * @param \MediaWiki\Extension\LDAPProvider\Client $client
	 * @param array $callbackRegistry
	 */
	public function __construct( $user, $domainConfig, $client, $callbackRegistry ) {
		$this->user = $user;
		$this->domainConfig = $domainConfig;
		$this->client = $client;
		$this->callbackRegistry = $callbackRegistry;
	}

	/**
	 * @return Status
	 */
	public function run() {
		$this->status = Status::newGood();
		try {
			$groups = $this->client->getUserGroups( $this->user->getName() );
			$syncMechanism = $this->makeSyncMechanism();
			$this->status = $syncMechanism->sync( $this->user, $groups, $this->domainConfig );
		} catch ( Exception $ex ) {
			$this->status = Status::newFatal( $ex->getMessage() );
		}

		return $this->status;
	}

	/**
	 *
	 * @return ISyncMechanism
	 * @throws MWException
	 */
	protected function makeSyncMechanism() {
		$syncMechanismKey = $this->domainConfig->get( 'mechanism' );
		// B\C;
		$callback = $syncMechanismKey;
		if ( isset( $this->callbackRegistry[$syncMechanismKey] ) ) {
			$callback = $this->callbackRegistry[$syncMechanismKey];
		}

		if ( !is_callable( $callback ) ) {
			throw new MWException( "Configured callback for '$syncMechanismKey' is invalid!" );
		}

		$logger = LoggerFactory::getInstance( 'LDAPGroups' );
		$syncMechanism = call_user_func_array( $callback, [ $this->domainConfig, $logger ] );

		if ( $syncMechanism instanceof ISyncMechanism === false ) {
			throw new MWException( "Configured callback for '$syncMechanismKey' did not return an"
				. " ISyncMechanism object!" );
		}

		return $syncMechanism;
	}

}

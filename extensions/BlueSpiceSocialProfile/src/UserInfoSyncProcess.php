<?php

namespace BlueSpice\Social\Profile;

use Exception;
use MediaWiki\Extension\LDAPProvider\Client;
use MediaWiki\Extension\LDAPUserInfo\Config;
use MediaWiki\Logger\LoggerFactory;
use MWException;
use Status;
use User;

class UserInfoSyncProcess {

	/**
	 *
	 * @var User
	 */
	private $user = null;

	/**
	 *
	 * @var Config
	 */
	private $domainConfig = null;

	/**
	 *
	 * @var array
	 */
	protected $callbackRegistry = [];

	/**
	 *
	 * @var Client
	 */
	private $client = null;

	/**
	 *
	 * @var ProfileFactory
	 */
	protected $profileFactory = null;

	/**
	 *
	 * @var CustomFieldsFactory
	 */
	protected $fieldFactory = null;

	/**
	 * UserInfoSyncProcess constructor.
	 * @param User $user
	 * @param Config $domainConfig
	 * @param MediaWiki\Extension\LDAPProvider\Client $client
	 * @param ProfileFactory $profileFactory
	 * @param CustomFieldsFactory $fieldFactory
	 */
	public function __construct( $user, $domainConfig, $client,
		ProfileFactory $profileFactory, CustomFieldsFactory $fieldFactory ) {
		$this->user = $user;
		$this->domainConfig = $domainConfig;
		$this->client = $client;
		$this->profileFactory = $profileFactory;
		$this->fieldFactory = $fieldFactory;
	}

	/**
	 * @return Status
	 */
	public function run() {
		$exception = null;
		try {
			$this->doSync();
		} catch ( MWException $ex ) {
			// For some reason, Exception catch block does not catch MWException
			$exception = $ex;
		} catch ( Exception $ex ) {
			$exception = $ex;
		}

		if ( $exception ) {
			$logger = LoggerFactory::getInstance( 'BlueSpiceSocialProfile' );
			$logger->error( $exception->getMessage() );
			return Status::newFatal( $exception->getMessage() );
		}

		return Status::newGood();
	}

	/**
	 * @return bool
	 * @throws \ConfigException
	 * @throws MWException
	 */
	private function doSync() {
		$logger = LoggerFactory::getInstance( 'BlueSpiceSocialProfile' );
		$userInfo = $this->client->getUserInfo( $this->user->getName() );
		if ( empty( $userInfo ) ) {
			return true;
		}

		$mapped = [];
		foreach ( $this->getSyncFieldDefinitions() as $name => $definition ) {
			$field = $this->fieldFactory->factory( $name, $this->user );
			if ( !$field ) {
				continue;
			}
			if ( !is_callable( $definition['ldap'] ) ) {
				$mapped[$name] = !empty( $userInfo[$definition['ldap']] )
					? $userInfo[$definition['ldap']]
					: '';
				continue;
			}
			$skip = false;
			$val = call_user_func_array( $definition['ldap'], [
				$name,
				$userInfo,
				$this->user,
				$field,
				&$skip
			] );
			if ( $skip || !is_scalar( $val ) ) {
				continue;
			}
			$mapped[$name] = $val;
		}

		if ( empty( $mapped ) ) {
			return true;
		}
		$profile = $this->profileFactory->newFromUser( $this->user );
		$profile->setValuesByObject( (object)$mapped );
		$profile->save( $this->user );
		return true;
	}

	/**
	 *
	 * @return array
	 */
	private function getSyncFieldDefinitions() {
		return array_filter(
			$this->fieldFactory->getFieldDefinitions(),
			function ( $e ) {
				return !empty( $e['ldap'] );
			}
		);
	}

}

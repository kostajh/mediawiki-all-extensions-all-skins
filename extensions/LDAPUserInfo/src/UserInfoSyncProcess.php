<?php

namespace MediaWiki\Extension\LDAPUserInfo;

use Exception;
use MediaWiki\Extension\LDAPProvider\Client;
use MediaWiki\Logger\LoggerFactory;
use MWException;
use Status;

class UserInfoSyncProcess {

	/**
	 *
	 * @var \User
	 */
	private $user = null;

	/**
	 *
	 * @var \Config
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
	 * @var Status
	 */
	private $status = null;

	/**
	 * UserInfoSyncProcess constructor.
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
			$logger = LoggerFactory::getInstance( 'LDAPUserInfo' );
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
		$logger = LoggerFactory::getInstance( 'LDAPUserInfo' );

		$userInfo = $this->client->getUserInfo( $this->user->getName() );
		$attributesMap = $this->domainConfig->get( Config::ATTRIBUTES_MAP );
		$modifierRegistry = $this->callbackRegistry;
		$hasChanges = false;

		foreach ( $attributesMap as $modifierKey => $ldapAttribute ) {
			if ( !isset( $userInfo[$ldapAttribute] ) ) {
				$logger->warning( "No attribute '$ldapAttribute' set in LDAP result!", $userInfo );
				continue;
			}

			$origModifierKey = $modifierKey;
			if ( !isset( $modifierRegistry[$modifierKey] ) ) {
				// "property.gender" --> "property.*"
				$modifierKey = preg_replace( '#^(.*?)\..*?#', '$1.*', $modifierKey );
				if ( !isset( $modifierRegistry[$modifierKey] ) ) {
					throw new MWException( "No factory callback set for '$modifierKey'!" );
				}
			}
			$factoryCallback = $modifierRegistry[$modifierKey];
			$modifier = call_user_func_array(
				$factoryCallback,
				[
					$origModifierKey,
					$this->domainConfig
				]
			);
			if ( $modifier instanceof IUserInfoModifier === false ) {
				throw new MWException( "Object from '$origModifierKey' callback does not "
					. "implement `IUserInfoModifier`!" );
			}

			if (
				!$modifier instanceof IUserInfoConditionalModifier ||
				$modifier->shouldModifyUserInfo( $this->user, $userInfo[$ldapAttribute ] )
			) {
				$hasChanges = true;
				$logger->info( "Set '$origModifierKey' with raw value {$userInfo[$ldapAttribute]}" );
				$modifier->modifyUserInfo( $this->user, $userInfo[$ldapAttribute] );
			}
		}

		if ( $hasChanges ) {
			$this->user->saveSettings();
		}

		return true;
	}

}

<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use ConfigException;
use MediaWiki\Extension\LDAPUserInfo\Config;
use MediaWiki\Extension\LDAPUserInfo\IUserInfoConditionalModifier;
use MediaWiki\Extension\LDAPUserInfo\IUserInfoModifier;
use Status;
use User;

class GenericProperty extends Base implements IUserInfoConditionalModifier {

	/**
	 *
	 * @var string
	 */
	protected $propertyName = '';

	/**
	 *
	 * @param \Config $domainConfig
	 * @param string $propertyName
	 */
	public function __construct( $domainConfig, $propertyName ) {
		parent::__construct( $domainConfig );
		$this->propertyName = $propertyName;
	}

	/**
	 *
	 * @param string $mappingKey e.g. "property.gender"
	 * @param \Config $domainConfig
	 * @return IUserInfoModifier
	 */
	public static function factory( $mappingKey, $domainConfig ) {
		$mappingKeyParts = explode( '.', $mappingKey, 2 );
		return new static( $domainConfig, $mappingKeyParts[1] );
	}

	/**
	 * @param User $user
	 * @param string $rawValue
	 * @return Status
	 * @throws ConfigException
	 */
	public function modifyUserInfo( $user, $rawValue ) {
		$user->setOption( $this->propertyName, $this->getNormalizedValue( $rawValue ) );
		return Status::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function shouldModifyUserInfo( $user, $rawValue ) {
		return $user->getOption( $this->propertyName ) !== $this->getNormalizedValue( $rawValue );
	}

	/**
	 * @param string $rawValue
	 * @return string
	 * @throws ConfigException
	 */
	protected function getNormalizedValue( $rawValue ) {
		$normalizationCallbacks = $this->domainConfig->get(
			Config::GENERIC_PROPERTY_NORMALIZATION_CALLBACKS
		);
		$value = $rawValue;
		if ( isset( $normalizationCallbacks[$this->propertyName] ) ) {
			$normalizationCallback = $normalizationCallbacks[$this->propertyName];
			if ( is_callable( $normalizationCallback ) ) {
				$value = call_user_func( $normalizationCallback, [ $rawValue ] );
			}
		}

		return $value;
	}
}

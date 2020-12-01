<?php
namespace BlueSpice\Social\Profile;

use MediaWiki\MediaWikiServices;

abstract class CustomField extends Field implements ICustomField {
	const KEY_DEFAULT = 'default';
	const KEY_REQUIRED = 'required';

	/**
	 *
	 * @var mixed
	 */
	protected $default = '';

	/**
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 *
	 * @param \Config $config
	 * @param string $name
	 * @param array $definition
	 * @param \User $user
	 */
	protected function __construct( $config, $name, $definition, $user ) {
		parent::__construct( $config, $name, $definition, $user );
		if ( isset( $definition[static::KEY_DEFAULT] ) ) {
			$this->default = $definition[static::KEY_DEFAULT];
		}
		if ( isset( $definition[static::KEY_REQUIRED] ) && $definition[static::KEY_REQUIRED] === true ) {
			$this->required = true;
		}
	}

	/**
	 * Returns the users profile entity
	 * @return Entity\Profile
	 */
	public function getUserProfile() {
		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		return $entityFactory->newFromUser( $this->user );
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->getUserProfile()->get( $this->getName(), $this->default );
	}

	/**
	 * Sets already validated values
	 * @param mixed $value
	 * @return type
	 */
	public function setValue( $value ) {
		return $this->getUserProfile()->set( $this->getName(), $value );
	}

	/**
	 * Validates a user input value
	 * @param mixed $value
	 * @return \Status
	 */
	public function validate( $value ) {
		$status = \Status::newGood( $value );
		if ( $this->required === true && empty( $value ) ) {
			$status->fatal( \Message::newFromKey(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getLabel()
			) );
		}
		return $status;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getDefault() {
		return $this->default;
	}
}

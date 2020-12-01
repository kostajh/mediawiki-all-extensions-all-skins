<?php

namespace BlueSpice\Social\Profile\CustomField;

use BlueSpice\Social\Profile\IFieldList;
use Message;
use Status;
use User;

class SelectValue extends StringValue implements IFieldList {
	const DFLT_TYPE = 'string';
	const KEY_OPTIONS = 'options';

	/**
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 *
	 * @param Config $config
	 * @param string $name
	 * @param array $definition
	 * @param User $user
	 */
	protected function __construct( $config, $name, $definition, $user ) {
		parent::__construct( $config, $name, $definition, $user );
		if ( isset( $definition[static::KEY_OPTIONS] ) ) {
			$this->options = $definition[static::KEY_OPTIONS];
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Validates a user input value
	 * @param mixed $value
	 * @return Status
	 */
	public function validate( $value ) {
		$status = parent::validate( $value );
		if ( !$status->isOK() ) {
			return $status;
		}
		if ( !empty( $value ) && !in_array( $value, $this->getOptions() ) ) {
			$status->fatal( Message::newFromKey(
				'bs-social-entity-fatalstatus-save-invalidfieldvalue',
				$this->getLabel()
			) );
		}
		return $status;
	}
}

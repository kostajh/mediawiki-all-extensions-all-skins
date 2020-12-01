<?php

namespace BlueSpice\Social\Profile\CustomField;

use Status;

class IntegerValue extends \BlueSpice\Social\Profile\CustomField {

	const DFLT_TYPE = 'int';

	protected $default = 0;

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

		if ( !is_numeric( $status->getValue() ) ) {
			return Status::newFatal(
				'bs-social-profile-field-validate-no-integer',
				$this->getName()
			);
		}
		$status->merge(
			Status::newGood( (int)$status->getValue() ),
			true
		);
		return $status;
	}
}

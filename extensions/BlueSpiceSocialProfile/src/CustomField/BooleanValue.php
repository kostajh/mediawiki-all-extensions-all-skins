<?php

namespace BlueSpice\Social\Profile\CustomField;

use Status;

class BooleanValue extends \BlueSpice\Social\Profile\CustomField {

	const DFLT_TYPE = 'boolean';

	protected $default = false;

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

		$status->merge(
			Status::newGood( $status->getValue() ? true : false ),
			true
		);
		return $status;
	}
}

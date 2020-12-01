<?php

namespace BlueSpice\Social\Profile\CustomField;

use BlueSpice\Social\Parser\Input;
use Status;

class StringValue extends \BlueSpice\Social\Profile\CustomField {

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

		$parser = new Input;
		$parser->parse( $status->getValue() );

		$status->merge(
			Status::newGood( $parser->parse( $status->getValue() ) ),
			true
		);
		return $status;
	}
}

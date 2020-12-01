<?php

namespace BlueSpice\ReadConfirmation;

use MWException;

class MechanismFactory {

	/**
	 * @var IMechanism
	 */
	private $mechanism = null;

	/**
	 * MechanismFactory constructor.
	 * @param string $mechanismCallback
	 * @throws MWException
	 */
	public function __construct( $mechanismCallback ) {
		if ( !is_callable( $mechanismCallback ) ) {
			throw new MWException(
				"There is no ReadConfirmation Mechanism"
			);
		}
		$this->mechanism = call_user_func( $mechanismCallback );
		if ( !$this->mechanism instanceof IMechanism ) {
			throw new MWException(
				(string)$mechanismCallback . " must implements IMechanism interface"
			);
		}
	}

	/**
	 * @return IMechanism
	 */
	public function getMechanismInstance() {
		return $this->mechanism;
	}
}

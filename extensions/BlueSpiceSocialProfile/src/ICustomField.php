<?php

namespace BlueSpice\Social\Profile;

interface ICustomField {
	/**
	 * @param mixed $value
	 * @return ICustomField
	 */
	public function setValue( $value );

	/**
	 * @param mixed $value
	 * @return \Status
	 */
	public function validate( $value );

	/**
	 * @return mixed
	 */
	public function getDefault();
}

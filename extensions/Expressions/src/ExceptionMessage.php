<?php

namespace Expressions;

class ExceptionMessage {
	/**
	 * @var string
	 */
	private $errormsg;

	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * ExceptionMessage constructor.
	 *
	 * @param string $errormsg
	 * @param array $parameters
	 */
	public function __construct( $errormsg, $parameters = [] ) {
		$this->errormsg = $errormsg;
		$this->parameters = $parameters;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errormsg;
	}

	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}
}

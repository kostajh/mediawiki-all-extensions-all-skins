<?php

namespace MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

class GenericCallback extends PreSearchUsernameModifier {

	/**
	 *
	 * @var callable
	 */
	private $callback = null;

	/**
	 * @param callable $callback
	 */
	public function __construct( $callback ) {
		$this->callback = $callback;
	}

	/**
	 *
	 * @param string $username
	 * @return string
	 */
	public function modify( $username ) {
		return call_user_func_array( $this->callback, [ $username ] );
	}
}

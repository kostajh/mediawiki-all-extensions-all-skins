<?php

namespace MediaWiki\Extension\LDAPProvider;

class TestClient extends Client {

	/**
	 *
	 * @var callable[]
	 */
	protected $callbacks = [];

	/**
	 * @param callable[] $callbacks to set up
	 */
	public function __construct( $callbacks ) {
		$this->callbacks = $callbacks;
	}

	/**
	 * @param string $username to check
	 * @param string $password to verify
	 * @return bool
	 */
	public function canBindAs( $username, $password ) {
		return call_user_func(
			$this->callbacks['canBindAs'], $username, $password
		);
	}

	/**
	 * @param string $match we're making
	 * @param string|null $basedn to use
	 * @param array $attrs to fetch
	 * @return array ??
	 */
	public function search( $match, $basedn = null, $attrs = [] ) {
		return call_user_func(
			$this->callbacks['search'], $match, $basedn, $attrs
		);
	}

	/**
	 * @param string $user to find groups for
	 * @param string $groupBaseDN to start searching at
	 * @return array
	 */
	public function getUserGroups( $user, $groupBaseDN = '' ) {
		return call_user_func(
			$this->callbacks['getUserGroups'], $user, $groupBaseDN
		);
	}

	/**
	 * @param string $username to find info for
	 * @param string $userBaseDN where to look
	 * @return array ??
	 */
	public function getUserInfo( $username, $userBaseDN = '' ) {
		return call_user_func(
			$this->callbacks['getUserInfo'], $username, $userBaseDN
		);
	}
}

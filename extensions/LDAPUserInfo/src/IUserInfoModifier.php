<?php

namespace MediaWiki\Extension\LDAPUserInfo;

use Status;
use User;

interface IUserInfoModifier {

	/**
	 *
	 * @param User $user
	 * @param string $rawValue
	 * @return Status
	 */
	public function modifyUserInfo( $user, $rawValue );
}

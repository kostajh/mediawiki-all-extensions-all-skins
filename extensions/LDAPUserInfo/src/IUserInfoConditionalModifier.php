<?php

namespace MediaWiki\Extension\LDAPUserInfo;

use User;

interface IUserInfoConditionalModifier extends IUserInfoModifier {

	/**
	 * Determine if the value should actually be updated
	 *
	 * @param User $user
	 * @param string $rawValue
	 * @return bool
	 */
	public function shouldModifyUserInfo( $user, $rawValue );
}

<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use MediaWiki\Extension\LDAPUserInfo\IUserInfoConditionalModifier;
use Status;
use User;

class Email extends Base implements IUserInfoConditionalModifier {

	/**
	 *
	 * @param User $user
	 * @param string $rawValue
	 * @return Status
	 */
	public function modifyUserInfo( $user, $rawValue ) {
		$user->setEmail( $rawValue );
		return Status::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function shouldModifyUserInfo( $user, $rawValue ) {
		return $user->getEmail() !== $rawValue;
	}
}

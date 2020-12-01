<?php

namespace MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

class ToLower extends PreSearchUsernameModifier {

	/**
	 *
	 * @param string $username
	 * @return string
	 */
	public function modify( $username ) {
		return strtolower( $username );
	}
}

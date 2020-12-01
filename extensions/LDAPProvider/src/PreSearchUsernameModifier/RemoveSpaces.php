<?php

namespace MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

class RemoveSpaces extends PreSearchUsernameModifier {

	/**
	 *
	 * @param string $username
	 * @return string
	 */
	public function modify( $username ) {
		return str_replace( " ", "", $username );
	}
}

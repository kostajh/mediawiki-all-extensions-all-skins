<?php

namespace MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier;

class SpacesToUnderscores extends PreSearchUsernameModifier {

	/**
	 *
	 * @param string $username
	 * @return string
	 */
	public function modify( $username ) {
		return str_replace( " ", "_", $username );
	}
}

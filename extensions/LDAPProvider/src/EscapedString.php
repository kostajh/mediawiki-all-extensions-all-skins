<?php

namespace MediaWiki\Extension\LDAPProvider;

class EscapedString {
	protected $value = '';

	/**
	 * Returns a string which has the chars *, (, ), \ & NUL escaped
	 * to LDAP compliant syntax as per RFC 2254 Thanks and credit to
	 * Iain Colledge for the research and function.
	 *
	 * Taken from original "Extension:LdapAuthentication" by Ryan Lane
	 *
	 * @param string $stringToEscape working with this
	 */
	public function __construct( $stringToEscape ) {
		$this->value = $stringToEscape;
	}

	/**
	 * The escaped string
	 * @return string
	 */
	public function __toString() {
		// Make the string LDAP compliant by escaping *, (, ) , \ & NUL
		return str_replace(
			[ "\\", "(", ")", "*", "\x00" ],
			[ "\\5c", "\\28", "\\29", "\\2a", "\\00" ],
			$this->value
		);
	}
}

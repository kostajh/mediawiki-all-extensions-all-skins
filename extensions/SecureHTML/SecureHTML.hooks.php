<?php
class SecureHTML {

	public static function parserInit( $parser ) {
		global $wgSecureHTMLTag;
		$parser->setHook( $wgSecureHTMLTag, array( __CLASS__, 'secureHTMLRender' ) );
	}

	public static function secureHTMLRender( $input, $argv, $parser, $frame ) {
		global $wgSecureHTMLSecrets;

		# The hash attribute is required.
		if ( !isset( $argv['hash'] ) ) {
			return( Html::rawElement( 'div', array( 'class' => 'error' ), wfMessage( 'securehtml-hashrequired' ) ) );
		}

		# If the array is empty, there is no possible way this will work.
		if ( count( $wgSecureHTMLSecrets ) === 0 ) {
			return( Html::rawElement( 'div', array( 'class' => 'error' ), wfMessage( 'securehtml-nokeys' ) ) );
		}

		# Get a list of key names.
		$keynames = array_keys( $wgSecureHTMLSecrets );

		# If the desired key name is not available, assume the first one.
		$keyname = ( isset( $argv['keyname'] ) ? $argv['keyname'] : $keynames[0] );

		# Key secret configuration.
		$keyalgorithm = 'sha256';
		if ( !array_key_exists( $keyname, $wgSecureHTMLSecrets ) ) {
			# To avoid leaking the existence of a key name by unauthorized users,
			# perform a dummy HMAC SHA256 (mitigate timing attacks), then
			# respond with "invalid hash", instead of something like "invalid key
			# name".
			$testhash = hash_hmac( 'sha256', $input, '' );
			return( Html::rawElement( 'div', array( 'class' => 'error' ), wfMessage( 'securehtml-invalidhash' ) ) );
		}
		if ( is_array( $wgSecureHTMLSecrets[$keyname] ) ) {
			if ( array_key_exists( 'secret', $wgSecureHTMLSecrets[$keyname] ) ) {
				$keysecret = $wgSecureHTMLSecrets[$keyname]['secret'];
			} else {
				return( Html::rawElement( 'div', array( 'class' => 'error' ), wfMessage( 'securehtml-invalidhash' ) ) );
			}
			if ( array_key_exists( 'algorithm', $wgSecureHTMLSecrets[$keyname] ) ) {
				$keyalgorithm = $wgSecureHTMLSecrets[$keyname]['algorithm'];
			}
		} else {
			$keysecret = $wgSecureHTMLSecrets[$keyname];
		}

		# Compute a test hash.
		$testhash = hash_hmac( $keyalgorithm, $input, $keysecret );

		# If the test hash matches the supplied hash, return the raw HTML.  Otherwise, error.
		if ( $testhash === $argv['hash'] ) {
			return( array( $input, 'markerType' => 'nowiki' ) );
		} else {
			return( Html::rawElement( 'div', array( 'class' => 'error' ), wfMessage( 'securehtml-invalidhash' ) ) );
		}
	}

}

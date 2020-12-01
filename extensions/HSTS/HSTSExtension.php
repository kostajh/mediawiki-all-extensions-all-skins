<?php

class HSTSExtension {

	/**
	 * Add the HSTS preference
	 *
	 * @param User $user Current user
	 * @param array &$preferences Description of the preferences
	 * @return bool true
	 */
	public static function getPreferences( $user, &$preferences ) {
		global $wgHSTSBetaFeature, $wgHSTSForUsers;

		// If HSTS is activated as a Beta Feature, do not add it here
		if ( $wgHSTSBetaFeature && class_exists( 'BetaFeatures' ) ) {
			return true;
		}

		// If HSTS is mandatory, do not display the choice
		if ( $wgHSTSForUsers ) {
			return true;
		}

		// Add the checkbox in the 'basic informations' section
		$preferences['hsts'] = [
			'type' => 'toggle',
			'label-message' => 'hsts-tog',
			'section' => 'personal/info'
		];

		// Enable this preference only if we are on HTTPS
		if ( $user->getRequest()->detectProtocol() !== 'https' ) {
			$preferences['hsts']['label-message'] = 'hsts-https-tog';
			$preferences['hsts']['disabled'] = true;
		}

		return true;
	}

	/**
	 * Add the HSTS beta feature
	 *
	 * @param User $user Current user
	 * @param array &$preferences Description of the Beta Features
	 * @return bool true
	 *
	 * @todo Add a screenshot (a padlock?)
	 */
	public static function getBetaFeaturePreferences( $user, &$preferences ) {
		global $wgHSTSBetaFeature, $wgHSTSForUsers;

		// If HSTS is activated as a Beta Feature, do not add it here
		if ( !$wgHSTSBetaFeature ) {
			return true;
		}

		// If HSTS is mandatory, do not display the choice
		if ( $wgHSTSForUsers ) {
			return true;
		}

		$preferences['hsts'] = [
			'label-message' => 'hsts-beta-feature-message',
			'desc-message' => 'hsts-beta-feature-description',
			'info-link' => 'https://www.mediawiki.org/wiki/Extension:HSTS',
			'discussion-link' => 'https://www.mediawiki.org/wiki/Extension_talk:HSTS',
			'requirements' => [ 'betafeatures' => [ 'prefershttps' ] ]
		];

		return true;
	}

	/**
	 * Add the STS header
	 *
	 * @param OutputPage $output Output page object
	 * @return bool true
	 */
	public static function addHeader( $output ) {
		global $wgHSTSForAnons, $wgHSTSForUsers, $wgHSTSIncludeSubdomains, $wgHSTSMaxAge;

		// Check if the user will get STS header
		if (
			$output->getRequest()->detectProtocol() !== 'https'
			|| ( $output->getUser()->isAnon() && !$wgHSTSForAnons )
			|| ( $output->getUser()->isLoggedIn() && !$wgHSTSForUsers && !$output->getUser()->getOption( 'hsts' ) )
		) {
			return true;
		}

		// Compute the max-age property
		if ( is_int( $wgHSTSMaxAge ) ) {
			$maxage = max( $wgHSTSMaxAge, 0 );
		} else {
			$maxage = wfTimestamp( TS_UNIX, $wgHSTSMaxAge );
			if ( $maxage !== false ) {
				$maxage -= wfTimestamp();
			} else {
				wfDebug( '[HSTS] Bad value of the parameter $wgHSTSMaxAge: must be an integer or a date.' );
				return true;
			}
			if ( $maxage < 0 ) {
				wfDebug( '[HSTS] Expired date; HSTS has been lost for all users, apart if externally added in the server configuration.' );
				return true;
			}
		}

		$header = 'Strict-Transport-Security: max-age=' . $maxage .
			( $wgHSTSIncludeSubdomains ? '; includeSubDomains' : '' );
		// Output the header
		$output->getRequest()->response()->header( $header );
		wfDebug( '[HSTS] ' . $header );

		return true;
	}
}

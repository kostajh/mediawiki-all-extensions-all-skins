<?php
/*
 * Copyright (C) 2017 Ike Hecht <tosfos@yahoo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Description of PreferencesListPreferences
 *
 * @author Ike Hecht <tosfos@yahoo.com>
 */
class PreferencesListPreferences {
	private static $preferenceLocations = [];

	/**
	 * A list of preference functions called in Preferences::getPreferences(), plus hookPreferences
	 *
	 * @var array
	 */
	private static $preferenceFunctions = [ 'profilePreferences', 'skinPreferences',
		'datetimePreferences', 'filesPreferences', 'renderingPreferences',
		'editingPreferences', 'rcPreferences', 'watchlistPreferences',
		'searchPreferences', 'miscPreferences', 'hookPreferences' ];
	private static $desiredPreferenceLocations;

	/**
	 * Gets the desired Preferences for the user requested.
	 *
	 * @throws MWException
	 * @param User $user
	 * @param array $desiredPref
	 * @param IContextSource $context
	 * @return array|null
	 */
	public static function getPreferences( $user, array $desiredPref, IContextSource $context ) {
		$defaultPreferences = [];

		// We really don't want to call all the preference functions as that is extremely slow.
		// So we'll only call the preference functions we need, which still adds extraneous work,
		// but it's faster.
		if ( !isset( self::$desiredPreferenceLocations ) ) {
			self::setDesiredPreferenceLocations( $user, $desiredPref, $context );
		}

		foreach ( self::$desiredPreferenceLocations as $desiredPreferenceLocation ) {
			if ( $desiredPreferenceLocation !== 'hookPreferences' ) {
				Preferences::{$desiredPreferenceLocation}( $user, $context, $defaultPreferences );
			} else {
				Hooks::run( 'GetPreferences', [ $user, &$defaultPreferences ] );
			}
		}

		/** @todo Make sure something matched */
		Preferences::loadPreferenceValues( $user, $context, $defaultPreferences );

		// Discard the unneeded preference fields
		$filteredPreferences = array_filter( $defaultPreferences,
			function ( $key ) use ( $desiredPref ) {
				return in_array( $key, $desiredPref );
			}, ARRAY_FILTER_USE_KEY
		);

		// PHP > 5.6
		return $filteredPreferences;
	}

	/**
	 * Store which Preference class functions will need to be called.
	 *
	 * @param User $user
	 * @param array $desiredPreferences
	 * @param IContextSource $context
	 */
	private static function setDesiredPreferenceLocations( User $user, array $desiredPreferences,
		IContextSource $context ) {
			self::$desiredPreferenceLocations = [];
		self::populatePreferenceLocations( $user, $context );
			foreach ( self::$preferenceLocations as $preferenceLocation => $preferencesArray ) {
				foreach ( $desiredPreferences as $desiredPreference ) {
					if ( array_search( $desiredPreference, $preferencesArray ) !== false ) {
						self::$desiredPreferenceLocations[] = $preferenceLocation;
					}
				}
			}
	}

	/**
	 * Populate the Preference Locations array for future reference, storing which preferences
	 * are set in which Preference class function.
	 *
	 * @param User $user
	 * @param IContextSource $context
	 */
	private static function populatePreferenceLocations( User $user, IContextSource $context ) {
		foreach ( self::$preferenceFunctions as $preferenceFunction ) {
			$$preferenceFunction = [];

			if ( $preferenceFunction !== 'hookPreferences' ) {
				Preferences::$preferenceFunction( $user, $context, $$preferenceFunction );
			} else {
				// Special handling for hook preferences
				Hooks::run( 'GetPreferences', [ $user, & $$preferenceFunction ] );
			}
			self::$preferenceLocations[$preferenceFunction] = array_keys( $$preferenceFunction );
		}
	}
}

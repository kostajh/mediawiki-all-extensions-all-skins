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
 * Description of PreferencesList
 *
 * @author Ike Hecht <tosfos@yahoo.com>
 */
class PreferencesList {
	/**
	 *
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * List of all preferences in the wiki in Form Field style
	 *
	 * @var array
	 */
	protected $allPreferences;

	/**
	 * Display options
	 */
	const CSV = 1;
	const TABLE = 2;

	/**
	 * Constructor
	 *
	 * @param IContextSource $context
	 * @param array $allPreferences
	 */
	public function __construct( IContextSource $context, array $allPreferences ) {
		$this->context = $context;
		$this->allPreferences = $allPreferences;
	}

	/**
	 * Fetch the Preferences for all Users in the wiki, and return a Form Fields style array
	 *
	 * @param array $preferenceNames
	 * @param IContextSource $context
	 * @return array
	 */
	private static function getAllUsersPreferences( array $preferenceNames, IContextSource $context ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'user', User::selectFields(), '', __METHOD__
		);
		$users = new UserArrayFromResult( $res );
		$preferencesArray = [];
		foreach ( $users as $user ) {
			$thisUsersPreferences = PreferencesListPreferences::getPreferences( $user, $preferenceNames,
					$context );
			foreach ( $preferenceNames as $preferenceName ) {
				// For some reason this is not always set. If it isn't, assume 0.
				if ( isset( $thisUsersPreferences[$preferenceName]['default'] ) ) {
					$preferencesArray[
						$user->getName()][$preferenceName] = $thisUsersPreferences[$preferenceName]['default'];
				} else {
					$preferencesArray[$user->getName()][$preferenceName] = 0;
				}
			}
			/** @todo is it safe to use User Name as Key? Maybe ID would be better. */
		}
		return $preferencesArray;
	}

	/**
	 * Get a list of preference fields that should not be displayed in the Preferences List
	 *
	 * @return array
	 */
	protected function getSkipFields() {
		return [ 'username', 'csv', 'password', 'emailauthentication', 'editwatchlist' ];
	}

	/**
	 * Based on the FormOptions, send back an array of Form Fields
	 *
	 * @param FormOptions $opts
	 * @return array
	 */
	public function getFormFields( FormOptions $opts ) {
		$skipFields = $this->getSkipFields();
		$allOptions = $opts->getAllValues();
		$formFields = [];
		$attributesWeWant = [ 'label', 'label-message', 'label-raw', 'section' ];
		foreach ( $allOptions as $key => $value ) {
			if ( in_array( $key, $skipFields ) ) {
				continue;
			}
			/** @todo It may save processing to do this in an overloading filterDataForSubmit() */
			$preferencesField = $this->allPreferences[$key];

			$formFields[$key]['type'] = 'check';
			$formFields[$key]['default'] = false;

			// Now take data from the standard field to suit our purposes.
			foreach ( $attributesWeWant as $field ) {
				if ( isset( $preferencesField[$field] ) ) {
					$formFields[$key][$field] = $preferencesField[$field];
				}
			}

			if ( isset( $preferencesField['label'] ) && $preferencesField['label'] == '&#160;' ) {
				// If the label is not set, use this preference's subsection as its label
				$sectionInfo = explode( '/', $preferencesField['section'] );
				/** @todo Use this skin's prefix */
				$formFields[$key]['label-message'] = 'prefs-' . $sectionInfo[1];
			}
		}

		// Add a CSV checkbox. This isn't a Preference.
		$formFields['csv'] = [ 'type' => 'check', 'label-message' => 'preferenceslist-csv' ];

		return $formFields;
	}

	/**
	 * Download a CSV of the results
	 *
	 * @param array $allUsersPreferences The data to be displayed
	 * @return bool
	 */
	protected function downloadCSV( array $allUsersPreferences ) {
		$rows = $this->getRows( $allUsersPreferences );

		// Take the $userName key and prepend it to the value array
		foreach ( $rows as $userName => &$userPrefs ) {
			// Special handling for the header row
			if ( $userName !== 0 ) {
				array_unshift( $userPrefs, $userName );
			}
		}

		PreferencesListUtils::arrayToCsvDownload( $rows );

		/** @todo Only return true if this worked */
		return true;
	}

	/**
	 * Get the rows of usernames and preference values to be displayed, including the header as
	 * the first row
	 *
	 * @param array $allUsersPreferences Preferences to be displayed
	 * @return array
	 */
	private function getRows( array $allUsersPreferences ) {
		// Create the header row
		$rows[0] = array_keys( reset( $allUsersPreferences ) );
		array_unshift( $rows[0], 'username' );

		foreach ( $allUsersPreferences as $userName => $userPrefs ) {
			foreach ( $userPrefs as $preferenceKey => $userPref ) {
				$rows[$userName][$preferenceKey] = $this->formatText( $userPref, $preferenceKey );
			}
		}
		return $rows;
	}

	/**
	 * Do special formatting for certain fields
	 *
	 * @param string $userPref What this preference was set to
	 * @param string $preferenceKey
	 * @return string
	 */
	protected function formatText( $userPref, $preferenceKey ) {
		$preferenceDescriptor = $this->allPreferences[$preferenceKey];
		// Make true/false preferences human-readable
		if ( isset( $preferenceDescriptor['type'] ) && $preferenceDescriptor['type'] === 'toggle' ) {
			if ( $userPref == '1' ) {
				return $this->context->msg( 'confirmable-yes' );
			} else {
				return $this->context->msg( 'confirmable-no' );
			}
		}

		if ( $preferenceKey === 'emailaddress' ) {
			// Strip change/add email address links
			// From https://stackoverflow.com/a/33865191
			$matches = [];
			if ( preg_match( "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $userPref, $matches ) ) {
				return $matches[0];
			} else {
				return '';
			}
		}
		return $userPref;
	}

	/**
	 * Show the subpage with correct preferences
	 * @param array $allUsersPreferences
	 * @return string
	 */
	protected function getTable( array $allUsersPreferences ) {
		$rows = $this->getRows( $allUsersPreferences );

		$html = Html::openElement( 'table', [ 'class' => 'wikitable' ] );

		$html .= Html::openElement( 'tr' );
		foreach ( $rows[0] as $label ) {
			$labelText = PreferencesListUtils::getMessage( $label, $this->allPreferences[$label],
					$this->context );
			$html .= Html::rawElement( 'th', [], $labelText );
		}
		$html .= Html::closeElement( 'tr' );
		unset( $rows[0] );

		foreach ( $rows as $userName => $userPrefs ) {
			$html .= Html::openElement( 'tr' );
			$html .= Html::element( 'th', [], $userName );
			foreach ( $userPrefs as $userPref ) {
				$html .= Html::rawElement( 'td', [], $userPref );
			}
			$html .= Html::closeElement( 'tr' );
		}
		$html .= Html::closeElement( 'table' );

		return $html;
	}

	/**
	 * Get a report of user names with their preferences
	 *
	 * @param array $preferencesToShow
	 * @param int $format One of the class integer constants
	 * @param IContextSource $context
	 * @return string|bool
	 */
	public function getResults( array $preferencesToShow, $format, IContextSource $context ) {
		$allUsersPreferences = self::getAllUsersPreferences(
				$preferencesToShow, $context );

		if ( $format === self::CSV ) {
			return $this->downloadCSV( $allUsersPreferences );
		} elseif ( $format === self::TABLE ) {
			return $this->getTable( $allUsersPreferences );
		}
	}
}

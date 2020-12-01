<?php

/**
 * SSStore, i.e. Site Settings Store - a store of values for a particular site.
 *
 * This is a generic container, that can be used by other extensions to
 * store values other than ones that came from the database and/or were set
 * by the user - for instance, to store the amount of file space taken by
 * files uploaded for this wiki.
 */

class SSStore {

	private static $fieldValues = array();

	static function addValue( $fieldName, $value ) {
		self::$fieldValues[$fieldName] = $value;
	}

	static function getValue( $fieldName ) {
		if ( ! array_key_exists( $fieldName, self::$fieldValues ) ) {
			self::loadValue( $fieldName );
		}
		return self::$fieldValues[$fieldName];
	}

	static function loadValue( $fieldName ) {
		Hooks::run( 'SiteSettingsStoreLoadValue', array( $fieldName, &$value ) );
		self::$fieldValues[$fieldName] = $value;
	}
}

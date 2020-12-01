<?php
/**
 * Contains class which offers functionality for reading and updating Translate group
 * related metadata
 *
 * @file
 * @author Niklas Laxström
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Niklas Laxström, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */

class TranslateMetadata {
	/** @var array Map of (group => key => value) */
	private static $cache = [];

	/**
	 * @param string[] $groups List of translate groups
	 */
	public static function preloadGroups( array $groups ) {
		$missing = array_keys( array_diff_key( array_flip( $groups ), self::$cache ) );
		if ( !$missing ) {
			return;
		}

		self::$cache += array_fill_keys( $missing, null ); // cache negatives

		$dbr = TranslateUtils::getSafeReadDB();
		$conds = count( $missing ) <= 500 ? [ 'tmd_group' => $missing ] : [];
		$res = $dbr->select(
			'translate_metadata',
			[ 'tmd_group', 'tmd_key', 'tmd_value' ],
			$conds,
			__METHOD__
		);
		foreach ( $res as $row ) {
			self::$cache[$row->tmd_group][$row->tmd_key] = $row->tmd_value;
		}
	}

	/**
	 * Get a metadata value for the given group and key.
	 * @param string $group The group name
	 * @param string $key Metadata key
	 * @return string|bool
	 */
	public static function get( $group, $key ) {
		self::preloadGroups( [ $group ] );

		return self::$cache[$group][$key] ?? false;
	}

	/**
	 * Get a metadata value for the given group and key.
	 * If it does not exist, return the default value.
	 * @param string $group
	 * @param string $key
	 * @param string $defaultValue
	 * @return string
	 */
	public static function getWithDefaultValue(
		string $group, string $key, string $defaultValue
	): string {
		$value = self::get( $group, $key );
		return $value === false ? $defaultValue : $value;
	}

	/**
	 * Set a metadata value for the given group and metadata key. Updates the
	 * value if already existing.
	 * @param string $group The group id
	 * @param string $key Metadata key
	 * @param string $value Metadata value
	 */
	public static function set( $group, $key, $value ) {
		$dbw = wfGetDB( DB_MASTER );
		$data = [ 'tmd_group' => $group, 'tmd_key' => $key, 'tmd_value' => $value ];
		if ( $value === false ) {
			unset( $data['tmd_value'] );
			$dbw->delete( 'translate_metadata', $data, __METHOD__ );
			unset( self::$cache[$group][$key] );
		} else {
			$dbw->replace(
				'translate_metadata',
				[ [ 'tmd_group', 'tmd_key' ] ],
				$data,
				__METHOD__
			);
			self::$cache[$group][$key] = $value;
		}
	}

	/**
	 * Wrapper for getting subgroups.
	 * @param string $groupId
	 * @return string[]|null
	 * @since 2012-05-09
	 */
	public static function getSubgroups( string $groupId ): ?array {
		$groups = self::get( $groupId, 'subgroups' );
		if ( is_string( $groups ) ) {
			if ( strpos( $groups, '|' ) !== false ) {
				$groups = explode( '|', $groups );
			} else {
				$groups = array_map( 'trim', explode( ',', $groups ) );
			}

			foreach ( $groups as $index => $id ) {
				if ( trim( $id ) === '' ) {
					unset( $groups[$index] );
				}
			}
		} else {
			$groups = null;
		}

		return $groups;
	}

	/**
	 * Wrapper for setting subgroups.
	 * @param string $groupId
	 * @param array $subgroupIds
	 * @since 2012-05-09
	 */
	public static function setSubgroups( $groupId, $subgroupIds ) {
		$subgroups = implode( '|', $subgroupIds );
		self::set( $groupId, 'subgroups', $subgroups );
	}

	/**
	 * Wrapper for deleting one wiki aggregate group at once.
	 * @param string $groupId
	 * @since 2012-05-09
	 */
	public static function deleteGroup( $groupId ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds = [ 'tmd_group' => $groupId ];
		$dbw->delete( 'translate_metadata', $conds, __METHOD__ );
		self::$cache[$groupId] = null;
	}
}

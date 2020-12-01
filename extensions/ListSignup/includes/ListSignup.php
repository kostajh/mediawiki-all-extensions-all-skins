<?php
/**
 * Implements the ListSignup class which manipulates the db
 *
 * @file
 * @ingroup SpecialPage
 */
class ListSignup {
	private static $mTableName = 'list_signup';

	static function addRow( array $row ) {
		$dbw = wfGetDB( DB_MASTER );
		$row['ls_timestamp'] = $dbw->timestamp();
		return $dbw->insert( self::$mTableName, $row );
	}

	static function purge( $fName = __METHOD__ ) {
		$dbw = wfGetDB( DB_MASTER );
		if ( !$dbw->tableExists( self::$mTableName, $fName ) ) {
			return false;
		}
		$sql = "TRUNCATE TABLE " . $dbw->tableName( self::$mTableName );

		return $dbw->query( $sql, $fName );
	}
}

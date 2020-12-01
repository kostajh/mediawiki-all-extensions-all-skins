<?php
/**
 * LoadBalancerSimpleSecurity always returns Database_SimpleSecurity regardles of $wgDBtype
 */
class LoadBalancerSimpleSecurity extends LoadBalancer {

	function reallyOpenConnection( array $server, $dbNameOverride = false ) {
		if( !is_array( $server ) ) {
			throw new MWException( 'You must update your load-balancing configuration. See DefaultSettings.php entry for $wgDBservers.' );
		}
		$host = $server['host'];
		$dbname = $server['dbname'];
		if ( $dbNameOverride !== false ) {
			$server['dbname'] = $dbname = $dbNameOverride;
		}
		wfDebug( "Connecting to $host $dbname...\n" );
		$db = new Database_SimpleSecurity(
			isset( $server['host'] ) ? $server['host'] : false,
			isset( $server['user'] ) ? $server['user'] : false,
			isset( $server['password'] ) ? $server['password'] : false,
			isset( $server['dbname'] ) ? $server['dbname'] : false,
			isset( $server['flags'] ) ? $server['flags'] : 0,
			isset( $server['tableprefix'] ) ? $server['tableprefix'] : 'get from global'
		);
		if ( $db->isOpen() ) {
			wfDebug( "Connected to $host $dbname.\n" );
		} else {
			wfDebug( "Connection failed to $host $dbname.\n" );
		}
		$db->setLBInfo( $server );
		return $db;
	}
}

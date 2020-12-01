<?php
/*
 * The new LBFactorySimpleSecurity class is identical to the LBFactorySimple class except that it returns a LoadBalancerSimpleSecurity object
 */
class LBFactorySimpleSecurity extends LBFactorySimple {

	function newMainLB( $wiki = false ) {
		global $wgDBservers, $wgMasterWaitTimeout;
		if ( $wgDBservers ) {
			$servers = $wgDBservers;
		} else {
			global $wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname, $wgDBtype, $wgDebugDumpSql;
			$servers = array(array(
				'host' => $wgDBserver,
				'user' => $wgDBuser,
				'password' => $wgDBpassword,
				'dbname' => $wgDBname,
				'type' => $wgDBtype,
				'load' => 1,
				'flags' => ($wgDebugDumpSql ? DBO_DEBUG : 0) | DBO_DEFAULT
			));
		}
		return new LoadBalancerSimpleSecurity( array(
			'servers' => $servers,
			'masterWaitTimeout' => $wgMasterWaitTimeout
		));
	}
}

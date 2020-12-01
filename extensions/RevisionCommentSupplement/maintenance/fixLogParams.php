<?php
/**
 * update log_params for update from 0.3.x backward
 *
 */

require_once( __DIR__ . '/../../../maintenance/Maintenance.php' );

class FixLogParams extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption( 'start', 'start time for seach' );
		$this->addOption( 'end', 'end time for seach' );
		$this->addOption( 'limit', 'max number of search rows' );
		$this->addDescription( 'update log_params for update from 0.3.x backward' );
		$this->requireExtension( 'RevisionCommentSupplement' );
	}

	public function execute() {
		$this->output( "\nThis will update 10 seconds after.\n" );
		$this->countDown( 10 );
		$i = 0;
		$dbw = $this->getDB( DB_MASTER );
		$conds = array(
			'log_type' => 'revisioncommentsupplement',
			'log_action' => array( 'create', 'delete', 'modify' ),
		);
		$option = array();
		if ( $this->hasOption( 'start' ) ) {
			$time = $dbw->timestamp( $this->getOption( 'start' ) );
			$time = $dbw->addQuotes( $time );
			$conds[] = "log_timestamp >= $time";
		}
		if ( $this->hasOption( 'end' ) ) {
			$time = $dbw->timestamp( $this->getOption( 'end' ) );
			$time = $dbw->addQuotes( $time );
			$conds[] = "log_timestamp <= $time";
		}
		if ( $this->hasOption( 'limit' ) ) {
			$option['LIMIT'] = $this->getOption( 'limit' );
		}
		$rows = $dbw->select( 'logging', 'log_id,log_params', $conds, __METHOD__, $option );
		foreach ( $rows as $row ) {
			$params = unserialize( $row->log_params );
			if ( array_key_exists( '5::comment1', $params ) ) {
				$params['5::oldsupplement'] = $params['5::comment1'];
				unset( $params['5::comment1'] );
			}
			if ( array_key_exists( '6::comment2', $params ) ) {
				$params['6::newsupplement'] = $params['6::comment2'];
				unset( $params['6::comment2'] );
			}
			ksort( $params );
			$params = serialize( $params );
			if ( $params != $row->log_params ) {
				$ret = $dbw->update(
					'logging',
					array( 'log_params' => $params ),
					array( 'log_id' => $row->log_id ),
					__METHOD__
				);
				if ( $ret ){
					$i++;
				}
			}
		}
		$this->output( "changed logs of $i entries.\n" );
		return true;
	}

}

$maintClass = 'FixLogParams';
require_once( RUN_MAINTENANCE_IF_MAIN );

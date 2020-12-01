<?php
/**
 * build rev_comment_supp_history table
 *
 */

require_once( __DIR__ . '/../../../maintenance/Maintenance.php' );

class buildHistory extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption( 'log', 'build from logging table' );
		$this->addOption( 'supplement', 'build from rev_comment_supp table' );
		$this->addOption( 'start', 'start time for seach' );
		$this->addOption( 'end', 'end time for seach' );
		$this->addOption( 'limit', 'max number of search rows' );
		$this->addDescription( 'build rev_comment_supp_history table' );
		$this->requireExtension( 'RevisionCommentSupplement' );
	}

	public function execute() {
		$this->output( "\nThis will build 10 seconds after.\n" );
		$this->countDown( 10 );
		if ( $this->hasOption( 'log' ) ) {
			$this->buildFromLogging();
		}
		if ( $this->hasOption( 'supplement' ) ) {
			$this->buildFromSupplement();
		}
		return true;
	}

	function buildFromLogging() {
		$i = 0;
		$j = 0;
		$dbw = $this->getDB( DB_MASTER );
		$conds = array(
			'log_type' => 'revisioncommentsupplement',
			'log_action' => array( 'create', 'create2', 'modify', 'modify2' ),
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
		$rows = $dbw->select( 'logging', '*', $conds, __METHOD__, $option );
		foreach ( $rows as $row ) {
			$params = unserialize( $row->log_params );
			if ( array_key_exists( '7::historyid', $params ) ) {
				continue;
			}
			$historyRow = array(
				'rcsh_rev_id' => $params['4::revid'],
				'rcsh_user' => $row->log_user,
				'rcsh_user_text' => $row->log_user_text,
				'rcsh_timestamp' => $row->log_timestamp
			);
			$histories = $dbw->select( 'rev_comment_supp_history', '*', $historyRow, __METHOD__ );
			if ( $histories === false || !$dbw->numRows( $histories ) ) {
				$logsupplement = '';
				if ( array_key_exists( '6::newsupplement', $params ) ) {
					$logsupplement = $params['6::newsupplement'];
				} elseif ( array_key_exists( '6::comment2', $params ) ) {
					$logsupplement = $params['6::comment2'];
				}
				$historyRow['rcsh_id'] = $dbw->nextSequenceValue(
					'rev_comment_supp_history_id_seq'
				);
				$historyRow['rcsh_supplement'] = $logsupplement;
				$historyRow['rcsh_reason'] = $row->log_comment;
				$ret = $dbw->insert( 'rev_comment_supp_history', $historyRow, __METHOD__ );
				if ( $ret ) {
					$i++;
				}
			} elseif ( $dbw->numRows( $histories ) == 1 ) {
				$history = $dbw->fetchObject( $histories );
				$logsupplement = false;
				if ( array_key_exists( '6::newsupplement', $params ) ) {
					$logsupplement = $params['6::newsupplement'];
				} elseif ( array_key_exists( '6::comment2', $params ) ) {
					$logsupplement = $params['6::comment2'];
				}
				$update = array();
				if ( $history->rcsh_supplement == '' && $logsupplement !== false ) {
					$update['rcsh_supplement'] = $logsupplement;
				}
				if ( $history->rcsh_reason == '' && $row->log_comment != '' ) {
					$update['rcsh_reason'] = $row->log_comment;
				}
				if ( ( $history->rcsh_hidden | $row->log_deleted ) != $history->rcsh_hidden ) {
					$update['rcsh_hidden'] = ( $history->rcsh_hidden | $row->log_deleted );
				}
				if ( count( $update ) ) {
					$dbw->update(
						'rev_comment_supp_history',
						$update,
						array( 'rcsh_id' => $history->rcsh_id ),
						__METHOD__
					);
					$j++;
				}
			}
		}
		$this->output( "added $i rows from logging table and modified $j rows.\n" );
	}

	function buildFromSupplement() {
		$i = 0;
		$j = 0;
		$dbw = $this->getDB( DB_MASTER );
		$conds = array();
		$option = array();
		if ( $this->hasOption( 'start' ) ) {
			$time = $dbw->timestamp( $this->getOption( 'start' ) );
			$time = $dbw->addQuotes( $time );
			$conds[] = "rcs_timestamp >= $time";
		}
		if ( $this->hasOption( 'end' ) ) {
			$time = $dbw->timestamp( $this->getOption( 'end' ) );
			$time = $dbw->addQuotes( $time );
			$conds[] = "rcs_timestamp <= $time";
		}
		if ( $this->hasOption( 'limit' ) ) {
			$option['LIMIT'] = $this->getOption( 'limit' );
		}
		$rows = $dbw->select( 'rev_comment_supp', '*', $conds, __METHOD__, $option );
		foreach ( $rows as $row ) {
			if ( isset( $row->rcs_latest ) ) {
				if ( $row->rcs_latest ) {
					continue;
				}
			}
			$historyRow = array(
				'rcsh_rev_id' => $row->rcs_rev_id,
				'rcsh_user' => $row->rcs_user,
				'rcsh_user_text' => $row->rcs_user_text,
				'rcsh_timestamp' => $row->rcs_timestamp
			);
			$histories = $dbw->select( 'rev_comment_supp_history', '*', $historyRow, __METHOD__ );
			if ( $histories === false || !$dbw->numRows( $histories ) ) {
				$historyRow['rcsh_id'] = $dbw->nextSequenceValue(
					'rev_comment_supp_history_id_seq'
				);
				$historyRow['rcsh_supplement'] = $row->rcs_supplement;
				$ret = $dbw->insert( 'rev_comment_supp_history', $historyRow, __METHOD__ );
				if ( $ret ) {
					$dbw->update(
						'rev_comment_supp',
						array( 'rcs_latest' => $dbw->insertId() ),
						array( 'rcs_rev_id' => $row->rcs_rev_id ),
						__METHOD__
					);
					$i++;
				}
			} elseif ( $dbw->numRows( $histories ) == 1 ) {
				$history = $dbw->fetchObject( $histories );
				if ( $history->rcsh_supplement == '' && $row->rcs_supplement != '' ) {
					$dbw->update(
						'rev_comment_supp_history',
						array( 'rcsh_supplement' => $row->rcs_supplement ),
						array( 'rcsh_id' => $history->rcsh_id ),
						__METHOD__
					);
					$j++;
				}
				$dbw->update(
					'rev_comment_supp',
					array( 'rcs_latest' => $history->rcsh_id ),
					array( 'rcs_rev_id' => $row->rcs_rev_id ),
					__METHOD__
				);
			}
		}
		$this->output( "added $i rows from rev_comment_supp table and modified $j rows.\n" );
	}
}

$maintClass = 'buildHistory';
require_once( RUN_MAINTENANCE_IF_MAIN );

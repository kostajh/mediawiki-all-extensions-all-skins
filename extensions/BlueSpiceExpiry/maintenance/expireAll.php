<?php

/**
 * Script to expire all pages (or all pages in defined namespaces)
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>, Benedikt Hofmann <hofmann@hallowelt.com>
 * @package    BlueSpice_Reminder
 * @subpackage Expiry
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

/**
 * Maintenance class to handle the expiration
 * @package BlueSpice_Reminder
 * @subpackage Expiry
 */
class ExpireAll extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Checks the needed reminder notifications for the day and send them." );
		$this->addOption( 'namespaces', 'Use only this namespaces' );
		$this->requireExtension( 'BlueSpiceExpiry' );
	}

	public function execute() {
		$aNamespaces = explode( ',', $this->getOption( 'namespaces', '' ) );

		$aConds = [];

		$sYesterday = date( 'Y-m-d', time() - 3600 * 24 );

		if ( !empty( $aNamespaces ) ) {
			$aConds = [ 'page_namespace' => $aNamespaces ];
		}

		$this->output( 'Search for needed pages ... ' );
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->select(
			'page',
			'page_id',
			$aConds,
			__METHOD__
		);
		$this->output( "Done\n" );

		$aPageIDs = [];

		foreach ( $res as $row ) {
			$aPageIDs[] = $row->page_id;
		}

		$res = $dbw->select(
			'bs_expiry',
			[ 'exp_page_id', 'exp_id', 'exp_date' ],
			[],
			__METHOD__
		);

		$aDoUpdate = [];
		$aDoNothing = [];

		foreach ( $res as $row ) {
			if ( $row->exp_date > $sYesterday ) {
				$aDoUpdate[$row->exp_page_id][] = $row->exp_id;
			} else {
				$aDoNothing[$row->exp_page_id][] = $row->exp_id;
			}
		}

		$this->output( 'Updating relevant pages ... ' );
		foreach ( $aPageIDs as $iPageID ) {
			if ( isset( $aDoUpdate[$iPageID] ) ) {
				$dbw->update(
					'bs_expiry',
					[ 'exp_date' => $sYesterday ],
					[ 'exp_id' => $aDoUpdate[$iPageID] ],
					__METHOD__
				);
			} elseif ( isset( $aDoNothing[$iPageID] ) ) {
				continue;
			} else {
				$dbw->insert(
					'bs_expiry',
					[
						'exp_page_id' => $iPageID,
						'exp_date' => $sYesterday
					],
					__METHOD__
				);
			}
		}

		$this->output( "Done\n" );
	}

}

$maintClass = ExpireAll::class;
require_once RUN_MAINTENANCE_IF_MAIN;

<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

class RestoreContenModel extends Maintenance {

	/**
	 *
	 * @var int
	 */
	protected $limit = 200;

	public function __construct() {
		parent::__construct();

		$this->addOption( 'execute', 'Really execute this script' );
	}

	public function execute() {
		$execute = $this->getOption( 'execute', false );

		if ( !defined( 'NS_SOCIALENTITY' ) ) {
			define( "NS_SOCIALENTITY", 1506 );
			$wgExtraNamespaces[NS_SOCIALENTITY] = 'SocialEntity';
		}
		if ( !defined( 'NS_SOCIALENTITY_TALK' ) ) {
			define( 'NS_SOCIALENTITY_TALK', 1507 );
			$wgExtraNamespaces[NS_SOCIALENTITY_TALK] = 'SocialEntity_talk';
		}
		$res = $this->getDB( DB_MASTER )->select(
			'page',
			'page_id',
			[ 'page_namespace' => NS_SOCIALENTITY, 'page_content_model' => CONTENT_MODEL_JSON ],
			__METHOD__
		);
		$this->output(
			"\nRestore \"BSSocial\" content model from {$res->numRows()} rows ...\n"
		);
		$step = $counter = 0;
		$steps = [];
		foreach ( $res as $row ) {
			$counter++;
			if ( $counter === $this->limit ) {
				$step++;
				$counter = 0;
			}
			$steps[$step][] = (int)$row->page_id;
		}

		foreach ( $steps as $step => $ids ) {
			$start = $step * $this->limit;
			$limit = $start + $this->limit > $res->numRows()
				? $res->numRows()
				: $start + $this->limit;
			$this->output( "$start - $limit ..." );
			$updated = true;
			if ( $execute ) {
				$updated = $this->getDB( DB_MASTER )->update(
					'page',
					[ 'page_content_model' => 'BSSocial' ],
					[
						'page_namespace' => NS_SOCIALENTITY,
						'page_content_model' => CONTENT_MODEL_JSON,
						'page_id' => $ids
					],
					__METHOD__
				);
			}
			if ( $updated ) {
				$this->output( "OK \n" );
				continue;
			}
			$this->output( "FAILED \n" );
		}

		$this->output( "\nDONE, GG" );
	}
}

$maintClass = 'RestoreContenModel';
require_once RUN_MAINTENANCE_IF_MAIN;

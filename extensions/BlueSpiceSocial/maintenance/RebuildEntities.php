<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

use BlueSpice\Social\Entity;
use MediaWiki\MediaWikiServices;

class RebuildEntities extends Maintenance {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->requireExtension( "BlueSpiceSocial" );

		$this->addOption( 'quick', 'Skip count down' );
	}

	/**
	 *
	 */
	public function execute() {
		$this->output( "\nThis may or may not fix all the problems...\n\n" );

		$user = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
		\RequestContext::getMain()->setUser( $user );

		foreach ( $this->getTitles() as $title ) {
			$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
				->newFromSourceTitle( $title );
			if ( !$entity instanceof Entity ) {
				continue;
			}
			$this->output( "\n{$entity->get( Entity::ATTR_ID )}..." );
			try {
				$status = $entity->save( $user );
				if ( !$status->isOK() ) {
					$this->output( $status->getMessage() );
					continue;
				}
			} catch ( \Exception $e ) {
				$this->output( $e->getMessage() );
				continue;
			}
			$this->output( "OK" );
		}
		$this->output( "\n\nDONE, GG" );
	}

	/**
	 *
	 * @param \Title[] $titles
	 * @return \Title[]
	 */
	protected function getTitles( $titles = [] ) {
		$res = $this->getDB( DB_REPLICA )->select(
			'page',
			[ 'page_id', 'page_title', 'page_namespace' ],
			[ 'page_namespace' => NS_SOCIALENTITY ],
			__METHOD__
		);
		if ( !$res ) {
			return [];
		}
		foreach ( $res as $row ) {
			$title = \Title::newFromRow( $row );
			if ( !$title ) {
				continue;
			}
			$titles[] = $title;
		}
		return $titles;
	}
}

$maintClass = 'rebuildEntities';
require_once RUN_MAINTENANCE_IF_MAIN;

<?php

require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/maintenance/Maintenance.php' );

/**
 * If in table oldimage oi_archive_name contains
 * multiple NS prefixes, this script fixes it
 */
class RemoveDuplicateNS extends Maintenance {

	function __construct() {
		parent::__construct();

		$this->requireExtension( 'NSFileRepo' );
	}

	function execute() {
		$dbw = wfGetDB( DB_MASTER );
		print( "USING DB: " . $dbw->getDBName() . "\n" );
		$images = $dbw->select( 'oldimage',
			array( 'oi_name', 'oi_archive_name' ),
			array(),
			__METHOD__
		);

		foreach( $images as $image ) {
			$archiveTS = explode( '!', $image->oi_archive_name )[0];
			$archiveName = explode( '!', $image->oi_archive_name )[1];
			$archiveBits = explode( ':', $archiveName );
			if( count($archiveBits) > 2 ) {
				$archiveNS = $archiveBits[0];
				$archiveFName = $archiveBits[count($archiveBits)-1];
				unset($archiveBits[count($archiveBits)-1] );
				$archiveFill = implode( ':', $archiveBits );
				print( "Removing: " . $archiveFill . PHP_EOL );
				$dbw->update(
					'oldimage',
					array(
						'oi_archive_name = ' . $dbw->strreplace( 'oi_archive_name',
							$dbw->addQuotes( $archiveFill ), $dbw->addQuotes( $archiveNS ) )
					),
					array( 'oi_name' => $image->oi_name ),
					__METHOD__
				);
			} else {
				continue;
			}
		}

	}
}

$maintClass = 'RemoveDuplicateNS';
require_once( RUN_MAINTENANCE_IF_MAIN );

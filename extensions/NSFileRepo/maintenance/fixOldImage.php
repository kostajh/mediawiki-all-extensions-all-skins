<?php

require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/maintenance/Maintenance.php' );

use MediaWiki\MediaWikiServices;

class FixOldImage extends Maintenance {
	protected $mDBName;

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

		$count = 0;
		$log = '';

		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			// MediaWiki 1.34+
			$repo = MediaWikiServices::getInstance()->getRepoGroup()->getRepo( 'local' );
		} else {
			$repo = RepoGroup::singleton()->getRepo( 'local' );
		}

		foreach( $images as $image ) {
			$nameBits = explode( ':', $image->oi_name );
			$nameNS = '';
			$nameName = $image->oi_name;
			if( count( $nameBits ) == 2 ) {
				$nameNS = $nameBits[0];
				$nameName = $nameBits[1];
 			}

			$archiveName = explode( '!', $image->oi_archive_name )[1];
			$archiveBits = explode( ':', $archiveName );
			$archiveNS = '';
			if( count( $archiveBits ) == 2 ) {
				$archiveNS = $archiveBits[0];
				$archiveName = $archiveBits[1];
			}

			$title = Title::makeTitle( NS_FILE, $image->oi_name );
			if( !$title || !$title->exists() ) {
				print( "Title wrong " . $image->oi_name . PHP_EOL );
				continue;
			}

			$strippedName = NSLocalFile::getFilenameStripped( $image->oi_archive_name );

			$file = OldLocalFile::newFromArchiveName( $title, $repo, $strippedName );
			if( !$file || !$file->exists() ) {
				$file = OldLocalFile::newFromArchiveName( $title, $repo, $image->oi_archive_name );
				if( $file && $file->exists() && file_exists( $file->getLocalRefPath() ) ) {
					$path = $file->getLocalRefPath();
					print( "Found wrong file on FS: " . $path . "..." );
					$baseDir = dirname( $path );
					$renamed = rename( $path, $baseDir . '/' . $strippedName );
					if( $renamed ) {
						print("renamed" . PHP_EOL );
						$log .= "Renamed " . $path . " to " . $baseDir . '/' . $strippedName . "\n";
					} else {
						print("failed to rename" . PHP_EOL );
						$log .= "Failed to rename " . $path . " to " . $baseDir . '/' . $strippedName . "\n";
					}

				}
			}

			if( $nameNS === $archiveNS && $nameName == $archiveName ) {
				continue;
			}

			if( $nameNS != $archiveNS && $nameName != $archiveName ) {
				continue;
			}

			print( "NAME: ns-" . $nameNS . ' name-' . $nameName . ' ARCHIVE: ns-' . $archiveNS . ' name-' . $archiveName . "\n" );
			$log .= $nameNS . ";" . $nameName . ";" . $archiveNS . ";" . $archiveName . "\n";
			$dbw->update(
				'oldimage',
				array(
					'oi_archive_name = ' . $dbw->strreplace( 'oi_archive_name',
						$dbw->addQuotes( $archiveName ), $dbw->addQuotes( $image->oi_name ) )
				),
				array( 'oi_archive_name' => $image->oi_archive_name ),
				__METHOD__
			);

			$count++;
		}
		file_put_contents( '/tmp/fixOldImagesLog.log', $log );
		print( "Total " . $count . " images altered\n" );
	}
}

$maintClass = 'FixOldImage';
require_once( RUN_MAINTENANCE_IF_MAIN );

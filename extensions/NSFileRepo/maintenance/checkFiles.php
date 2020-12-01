<?php

require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/maintenance/Maintenance.php' );

use MediaWiki\MediaWikiServices;

/**
 * This script checks if all files in DB have their
 * counterpart on FileSystem
 */
class CheckFiles extends Maintenance {

	function __construct() {
		parent::__construct();

		$this->requireExtension( 'NSFileRepo' );
	}

	function execute() {
		$dbr = wfGetDB( DB_REPLICA );
		print( "Using DB: " . $dbr->getDBName() ) . PHP_EOL;

		$aImgNames = array();
		$res = $dbr->select('image',
			array( 'img_name' ),
			array(),
			__METHOD__
		);

		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			// MediaWiki 1.34+
			$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		} else {
			$repoGroup = RepoGroup::singleton();
		}

		foreach( $res as $row ) {
			$sName = preg_replace( '/_/', ' ', $row->img_name );
			$oTitle = Title::makeTitle( NS_FILE, $sName );

			if( !$oTitle->exists() ) {
					print ( "Title for " . $sName . " does not exist" . PHP_EOL );
					continue;
			}

			if( $oTitle->getNamespace() !== NS_FILE ) {
					print ( "Title for " . $sName . " is not in NS_FILE" . PHP_EOL );
					continue;
			}

			$oFile = $repoGroup->findFile( $sName );
			if( !$oFile || !$oFile->exists() ) {
					print( "File " . $sName . " does not exist!" . PHP_EOL );
			}
			$sFileLocalPath = $oFile->getLocalRefPath();
			if( !$sFileLocalPath || !file_exists( $sFileLocalPath ) ) {
				print( "Image " . $sName . " not found!" . PHP_EOL );
			}
		}

		$res = $dbr->select('oldimage',
			array( 'oi_name', 'oi_archive_name' ),
			array(),
			__METHOD__
		);

			foreach( $res as $row ) {
			$oTitle = Title::makeTitle( NS_FILE, $row->oi_name );
			$repo = $repoGroup->getRepo( 'local' );
			$strippedName = NSLocalFile::getFilenameStripped( $row->oi_archive_name );
			$file = OldLocalFile::newFromArchiveName( $oTitle, $repo, $strippedName );
			if( !$file->getLocalRefPath() || !file_exists( $file->getLocalRefPath() ) ) {
				$file = OldLocalFile::newFromArchiveName( $oTitle, $repo, $row->oi_archive_name );
				print( "Archive file: " . $row->oi_archive_name . " not found" . PHP_EOL );
				if( $file->getLocalRefPath() && file_exists( $file->getLocalRefPath() ) ) {
					print( "\t...but wrong version of this file exists: " . $file->getLocalRefPath() . PHP_EOL );
				}
			}
		}
	}
}

$maintClass = 'CheckFiles';
require_once( RUN_MAINTENANCE_IF_MAIN );

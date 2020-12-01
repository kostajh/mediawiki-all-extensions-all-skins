<?php

class NSLocalFile extends LocalFile {
	/**
	 * Get the path of the file relative to the public zone root
	 */
	function getRel() {
		return $this->getHashPath() . self::getFileNameStripped( $this->getName() );
	}
	/**
	 * Get the path, relative to the thumbnail zone root, of the
	 * thumbnail directory or a particular file if $suffix is specified
	 *
	 * @param $suffix bool|string if not false, the name of a thumbnail file
	 *
	 * @return string
	 */
	function getThumbRel( $suffix = false ) {
		$path = $this->getRel();
		if ( $suffix !== false ) {
			/* This is the part that changed from LocalFile */
			$path .= '/' . self::getFileNameStripped( $suffix );
			/* End of changes */
		}
		return $path;
	}

	/**
	 * Get the path of an archived file relative to the public zone root
	 *
	 * @param $suffix bool|string if not false, the name of an archived thumbnail file
	 *
	 * @return string
	 */
	function getArchiveRel( $suffix = false ) {
		$path = 'archive/' . $this->getHashPath();
		if ( $suffix === false ) {
			$path = substr( $path, 0, -1 );
		} else {
/* This is the part that changed from LocalFile */
			$path .= '/' . self::getFileNameStripped( $suffix );
/* End of changes */
		}
		return $path;
	}



	/**
	 * Get urlencoded relative path of the file
	 */
	function getUrlRel() {
		return $this->getHashPath() .
			rawurlencode( self::getFileNameStripped( $this->getName() ) );
	}

	/**
	 * Get the URL of the thumbnail directory, or a particular file if $suffix is specified
	 *
	 * @param $suffix bool|string if not false, the name of a thumbnail file
	 *
	 * @return string path
	 */
	function getThumbUrl( $suffix = false ) {
		$path = $this->repo->getZoneUrl('thumb') . '/' . $this->getUrlRel();
		if ( $suffix !== false ) {
			$path .= '/' . rawurlencode( self::getFileNameStripped( $suffix ) );
		}
		return $path;
	}


	public function thumbName( $params, $flags = 0 ) {
		$name = ( $this->repo && !( $flags & self::THUMB_FULL_NAME ) )
/* This is the part that changed from LocalFile */
			? $this->repo->nameForThumb( self::getFileNameStripped( $this->getName() ) )
			: self::getFileNameStripped( $this->getName() );
/* End of changes */
		return $this->generateThumbName( $name, $params );
	}

	/**
	 * Generate a thumbnail file name from a name and specified parameters
	 *
	 * @param string $name
	 * @param array $params Parameters which will be passed to MediaHandler::makeParamString
	 *
	 * @return string
	 */
	public function generateThumbName( $name, $params ) {
		if ( !$this->getHandler() ) {
			return null;
		}
		$extension = $this->getExtension();
		list( $thumbExt, $thumbMime ) = $this->handler->getThumbType(
			$extension, $this->getMimeType(), $params );
/* This is the part that changed from LocalFile */
		$thumbName = $this->handler->makeParamString( $params ) . '-' .
			self::getFileNameStripped( $this->getName() );
/* End of changes */
		if ( $thumbExt != $extension ) {
			$thumbName .= ".$thumbExt";
		}
/* And also need to retain namespace changed from LocalFile */
		$bits = explode( ':',$this->getName() );
		if ( count($bits) > 1 ) $thumbName = $bits[0] . ":" . $thumbName;
/* End of changes */
		return $thumbName;
	}

	/**
	 * Get the URL of the archive directory, or a particular file if $suffix is specified
	 *
	 * @param $suffix bool|string if not false, the name of an archived file
	 *
	 * @return string
	 */
	function getArchiveUrl( $suffix = false ) {
		$this->assertRepoDefined();
		$ext = $this->getExtension();
		$path = $this->repo->getZoneUrl( 'public', $ext ) . '/archive/' . $this->getHashPath();
		if ( $suffix === false ) {
			$path = substr( $path, 0, -1 );
		} else {
/* This is the part that changed from LocalFile */
			$path .= rawurlencode( self::getFileNameStripped( $suffix ) );
/* End of changes */
		}
		return $path;
	}

	/**
	 * Get the path, relative to the thumbnail zone root, for an archived file's thumbs directory
	 * or a specific thumb if the $suffix is given.
	 *
	 * @param string $archiveName The timestamped name of an archived image
	 * @param bool|string $suffix If not false, the name of a thumbnail file
	 * @return string
	 */
	function getArchiveThumbRel( $archiveName, $suffix = false ) {
		$path = 'archive/' . $this->getHashPath() . $archiveName . "/";
		if ( $suffix === false ) {
			$path = substr( $path, 0, -1 );
		} else {
/* This is the part that changed from LocalFile */
			$path .= self::getFileNameStripped( $suffix );
/* End of changes */
		}

		return $path;
	}

	/**
	 * Get the URL of the archived file's thumbs, or a particular thumb if $suffix is specified
	 *
	 * @param string $archiveName The timestamped name of an archived image
	 * @param bool|string $suffix If not false, the name of a thumbnail file
	 * @return string
	 */
	function getArchiveThumbUrl( $archiveName, $suffix = false ) {
		$this->assertRepoDefined();
		$ext = $this->getExtension();
		$path = $this->repo->getZoneUrl( 'thumb', $ext ) . '/archive/' .
			$this->getHashPath() . rawurlencode( $archiveName ) . "/";
		if ( $suffix === false ) {
			$path = substr( $path, 0, -1 );
		} else {
/* This is the part that changed from LocalFile */
			$path .= rawurlencode( self::getFileNameStripped( $suffix ) );
/* End of changes */
		}

		return $path;
	}

	/**
	 * Delete cached transformed files for the current version only.
	 * @param array $options
	 */
	protected function purgeThumbList( $dir, $files ) {

		$purgeList = [];
		foreach ( $files as $file ) {
			if ( $this->repo->supportsSha1URLs() ) {
				$reference = $this->getSha1();
			} else {
				//change from LocalFile.php here
				$reference = $this->getFileNameStripped($this->getName());
			}

			# Check that the reference (filename or sha1) is part of the thumb name
			# This is a basic sanity check to avoid erasing unrelated directories
			if ( strpos( $file, $reference ) !== false
				|| strpos( $file, "-thumbnail" ) !== false // "short" thumb name
			) {
				$purgeList[] = "{$dir}/{$file}";
			}
		}

		# Delete the thumbnails
		$this->repo->quickPurgeBatch( $purgeList );
		# Clear out the thumbnail directory if empty
		$this->repo->quickCleanDir( $dir );
	}
	/**
	 * Get the public zone virtual URL for an archived version source file
	 *
	 * @param $suffix bool|string if not false, the name of a thumbnail file
	 *
	 * @return string
	 */
	function getArchiveVirtualUrl( $suffix = false ) {
		$this->assertRepoDefined();
		$path = $this->repo->getVirtualUrl() . '/public/archive/' . $this->getHashPath();
		if ( $suffix === false ) {
			$path = substr( $path, 0, -1 );
		} else {
/* This is the part that changed from LocalFile */
			$path .= rawurlencode( self::getFileNameStripped( $suffix ) );
/* End of changes */
		}
		return $path;
	}

	/**
	 * Get the virtual URL for a thumbnail file or directory
	 *
	 * @param $suffix bool|string if not false, the name of a thumbnail file
	 *
	 * @return string
	 */
	function getThumbVirtualUrl( $suffix = false ) {
		$this->assertRepoDefined();
		$path = $this->repo->getVirtualUrl() . '/thumb/' . $this->getUrlRel();
		if ( $suffix !== false ) {
			$path .= '/' . rawurlencode( $suffix );
/* This is the part that changed from LocalFile */
			$path .= '/' . rawurlencode( self::getFileNameStripped( $suffix ) );
/* End of changes */
		}
		return $path;
	}

	/**
	 * Strip namespace (if any) from file name
	 *
	 * @param $suffix the name of a thumbnail file
	 *
	 * @return string
	 */
	public static function getFileNameStripped($suffix) {
		$iNsEndPos = strpos( $suffix, ":");
		if( $iNsEndPos ){
			$sRes = '';
			$iTsEndPos = strpos( $suffix, "!" );

			if( $iTsEndPos ) {
				$sRes = substr( $suffix, 0, $iTsEndPos +1 );
			}
			$sRes .= substr( $suffix,  $iNsEndPos +1, strlen( $suffix ) -1 );

			return $sRes;
		} else {
			return $suffix;
		}
	}

	/**
	 * Move or copy a file to a specified location. Returns a FileRepoStatus
	 * object with the archive name in the "value" member on success.
	 *
	 * The archive name should be passed through to recordUpload for database
	 * registration.
	 *
	 * @param $srcPath String: local filesystem path to the source image
	 * @param $dstRel String: target relative path
	 * @param $flags Integer: a bitwise combination of:
	 *     File::DELETE_SOURCE	Delete the source file, i.e. move rather than copy
	 * @param $options array Optional additional parameters
	 * @return Status object. On success, the value member contains the
	 *     archive name, or an empty string if it was a new file.
	 */
	function publishTo( $srcPath, $dstRel, $flags = 0, array $options = array() ) {
		if ( $this->getRepo()->getReadOnlyReason() !== false ) {
			return $this->readOnlyFatalStatus();
		}

		$this->lock(); // begin

		$archiveName = wfTimestamp( TS_MW ) . '!'. $this->getName();
/* This is the part that changed from LocalFile */
		$strippedArchiveName = wfTimestamp( TS_MW ) . '!'. self::getFileNameStripped( $this->getName() );
		$archiveRel = 'archive/' . $this->getHashPath() . $strippedArchiveName;
/* End of changes */
		$flags = $flags & File::DELETE_SOURCE ? LocalRepo::DELETE_SOURCE : 0;
		$status = $this->repo->publish( $srcPath, $dstRel, $archiveRel, $flags, $options );

		if ( $status->value == 'new' ) {
			$status->value = '';
		} else {
			$status->value = $archiveName;
		}

		$this->purgeThumbnails();
		$this->unlock(); // done

		return $status;
	}

	/**
	 * Move file to the new title
	 *
	 * Move current, old version and all thumbnails
	 * to the new filename. Old file is deleted.
	 *
	 * Cache purging is done; checks for validity
	 * and logging are caller's responsibility
	 *
	 * @param $target Title New file name
	 * @return Status object.
	 */
	function move( $target ) {
		if ( $this->getRepo()->getReadOnlyReason() !== false ) {
			return $this->readOnlyFatalStatus();
		}

		wfDebugLog( 'imagemove', "Got request to move {$this->name} to " . $target->getText() );
/* This is the part that changed from LocalFile */
		$batch = new NSLocalFileMoveBatch( $this, $target );
/* End of changes */

		$this->lock(); // begin
		$batch->addCurrent();
		$archiveNames = $batch->addOlds();
		$status = $batch->execute();
		$this->unlock(); // done

		wfDebugLog( 'imagemove', "Finished moving {$this->name}" );

		$this->purgeEverything();
		foreach ( $archiveNames as $archiveName ) {
			$this->purgeOldThumbnails( $archiveName );
		}
		if ( $status->isOK() ) {
			// Now switch the object
			$this->title = $target;
			// Force regeneration of the name and hashpath
			unset( $this->name );
			unset( $this->hashPath );
			// Purge the new image
			$this->purgeEverything();
		}

		return $status;
	}


	/** Instantiating this class using "self"
	 * If you're reading this, you're problably wondering why on earth are the following static functions, which are copied
	 * verbatim from the original extended class "LocalFIle" included here?
	 * The answer is that "self", will instantiate the class the code is physically in, not the class extended from it.
	 * Without the inclusion of these methods in "NSLocalFile, "self" would instantiate a "LocalFile" class, not the
	 * "NSLocalFile" class we want it to.  Since there are only two methods within the "LocalFile" class that use "self",
	 * I just copied that code into the new "NSLocalFile" extended class, and the copied code will instantiate the "NSLocalFIle"
	 * class instead of the "LocalFile" class (at least in PHP 5.2.4)
	 */

	/**
	 * Create a NSLocalFile from a title
	 * Do not call this except from inside a repo class.
	 *
	 * Note: $unused param is only here to avoid an E_STRICT
	 */
	static function newFromTitle( $title, $repo, $unused = null ) {
		return new self( $title, $repo );
	}
	/**
	 * Create a NSLocalFile from a title
	 * Do not call this except from inside a repo class.
	 */

	static function newFromRow( $row, $repo ) {
		$title = Title::makeTitle( NS_FILE, $row->img_name );
		$file = new self( $title, $repo );
		$file->loadFromRow( $row );
		return $file;
	}

	/**
	 * Create a NSLocalFile from a SHA-1 key
	 * Do not call this except from inside a repo class.
	 *
	 * Copy & paste from LocalFile to fix "late-static-binding" issue
	 *
	 * @param string $sha1 Base-36 SHA-1
	 * @param LocalRepo $repo
	 * @param string|bool $timestamp MW_timestamp (optional)
	 * @return bool|LocalFile
	 */
	static function newFromKey( $sha1, $repo, $timestamp = false ) {
		$dbr = $repo->getReplicaDB();

		$conds = [ 'img_sha1' => $sha1 ];
		if ( $timestamp ) {
			$conds['img_timestamp'] = $dbr->timestamp( $timestamp );
		}

		$fileQuery = static::getQueryInfo();
		$row = $dbr->selectRow(
			$fileQuery['tables'], $fileQuery['fields'], $conds, __METHOD__, [], $fileQuery['joins']
		);
		if ( $row ) {
			return static::newFromRow( $row, $repo );
		} else {
			return false;
		}
	}
}

/**
 * Helper class for file movement
 * @ingroup FileAbstraction
 */
class NSLocalFileMoveBatch extends LocalFileMoveBatch {
	/**
	 * @param File $file
	 * @param Title $target
	 */
	function __construct( File $file, Title $target ) {
		$this->file = $file;
		$this->target = $target;
		$repo = $file->getRepo();
		$this->oldHash = $repo->getHashPath( $this->file->getName() );
		$this->newHash = $repo->getHashPath( $this->target->getDBkey() );
		$this->oldName = $this->file->getName();
		$this->newName = $repo->getNameFromTitle( $this->target );
		$this->oldRel = $this->oldHash . NSLocalFile::getFileNameStripped( $this->oldName );
		$this->newRel = $this->newHash . NSLocalFile::getFileNameStripped( $this->newName );
		$this->db = $repo->getMasterDB();
	}

	/**
	 * Add the old versions of the image to the batch
	 * @return array List of archive names from old versions
	 */
	function addOlds() {
/* This is the part that changed from LocalFile */
		$newName = $this->getFileNameStripped( $this->newName );
/* End of changes */
		$archiveBase = 'archive';
		$this->olds = array();
		$this->oldCount = 0;
		$archiveNames = array();

		$result = $this->db->select( 'oldimage',
			array( 'oi_archive_name', 'oi_deleted' ),
			array( 'oi_name' => $this->oldName ),
			__METHOD__
		);

		foreach ( $result as $row ) {
			$archiveNames[] = $row->oi_archive_name;
			$oldName = $row->oi_archive_name;
			$bits = explode( '!', $oldName, 2 );

			if ( count( $bits ) != 2 ) {
				wfDebug( "Old file name missing !: '$oldName' \n" );
				continue;
			}

			list( $timestamp, $filename ) = $bits;

			if ( $this->oldName != $filename ) {
				wfDebug( "Old file name doesn't match: '$oldName' \n" );
				continue;
			}
/* This is the part that changed from LocalFileMoveBatch */
			#When file is moved within a namespace we do not want it
			#looking to NS:Name format in FS
			$strippedOldName = $this->file->getFileNameStripped( $oldName );
/* End of changes */

			$this->oldCount++;

			// Do we want to add those to oldCount?
			if ( $row->oi_deleted & File::DELETED_FILE ) {
				continue;
			}
/* This is the part that changed from LocalFile */
			$this->olds[] = array(
				"{$archiveBase}/{$this->oldHash}{$strippedOldName}",
				"{$archiveBase}/{$this->newHash}{$timestamp}!{$newName}"
			);
/* End of changes */
		}

		return $archiveNames;
	}

	function getFileNameStripped( $suffix ) {
		return(NSLocalFile::getFileNameStripped($suffix));
	}
}

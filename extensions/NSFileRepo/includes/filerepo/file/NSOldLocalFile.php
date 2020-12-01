<?php

class NSOldLocalFile extends OldLocalFile {

	/**
	 *
	 * @var NSLocalFile
	 */
	protected $internalFile = null;

	public function __construct($title, $repo, $time, $archiveName) {
		parent::__construct($title, $repo, $time, $archiveName);
		$this->internalFile = new NSLocalFile($title, $repo);
	}

	function getRel() {
		return 'archive/' . $this->getHashPath() .
			$this->getFileNameStripped( $this->getArchiveName() );
	}
	function getUrlRel() {
		return 'archive/' . $this->getHashPath() .
			urlencode( $this->getFileNameStripped( $this->getArchiveName() ) );
	}
	function publish( $srcPath, $flags = 0, array $options = array() ) {
		return $this->internalFile->publish( $srcPath, $flags, $options );
	}
	function getThumbUrl( $suffix = false ) {
		return $this->internalFile->getArchiveThumbUrl( $this->getFileNameStripped( $this->getArchiveName() ), $suffix );
	}
	function thumbName( $params, $flags = 0 ) {
		return $this->internalFile->thumbName( $params, $flags );
	}
	function getThumbPath( $suffix = false ) {
		return $this->internalFile->getArchiveThumbPath( $this->getFileNameStripped( $this->getArchiveName() ), $suffix );
	}
	function getArchiveRel( $suffix = false ) {
		return $this->internalFile->getArchiveRel( $suffix );
	}
	function getArchiveUrl( $suffix = false ) {
		return $this->internalFile->getArchiveUrl( $suffix );
	}
	function getArchiveVirtualUrl( $suffix = false ) {
		return $this->internalFile->getArchiveVirtualUrl( $suffix );
	}
	function getThumbVirtualUrl( $suffix = false ) {
		return $this->internalFile->getThumbVirtualUrl( $suffix );
	}
	function getVirtualUrl( $suffix = false ) {
		return $this->internalFile->getVirtualUrl( $suffix );
	}
	function getFileNameStripped($suffix) {
		return $this->internalFile->getFileNameStripped( $suffix );
	}
	function addOlds() {
		return $this->internalFile->addOlds();
	}
	function purgeThumbnails($options = array() ) {
		return $this->internalFile->purgeThumbnails( $options );
	}
	/**
	 * Replaces hard coded OldLocalFile::newFromRow to use $this->repo->oldFileFromRowFactory configuration
	 * This may not be necessary in the future if LocalFile is patched to allow configuration
	*/
	function getHistory( $limit = null, $start = null, $end = null, $inc = true ) {
		return $this->internalFile->getHistory( $limit, $start , $end, $inc );
	}

	/** See comment above about Instantiating this class using "self" */

	static function newFromTitle( $title, $repo, $time = null ) {
		# The null default value is only here to avoid an E_STRICT
		if( $time === null )
			throw new MWException( __METHOD__.' got null for $time parameter' );
		return new self( $title, $repo, $time, null );
	}

	static function newFromArchiveName( $title, $repo, $archiveName ) {
		return new self( $title, $repo, null, $archiveName );
	}

	static function newFromRow( $row, $repo ) {
		$title = Title::makeTitle( NS_FILE, $row->oi_name );
		$file = new self( $title, $repo, null, $row->oi_archive_name );
		$file->loadFromRow( $row, 'oi_' );
		return $file;
	}

	/**
	 * Create a OldLocalFile from a SHA-1 key
	 * Do not call this except from inside a repo class.
	 *
	 * Copy & paste from OldLocalFile to fix "late-static-binding" issue
	 *
	 * @param string $sha1 Base-36 SHA-1
	 * @param LocalRepo $repo
	 * @param string|bool $timestamp MW_timestamp (optional)
	 *
	 * @return bool|OldLocalFile
	 */
	static function newFromKey( $sha1, $repo, $timestamp = false ) {
		$dbr = $repo->getReplicaDB();

		$conds = [ 'oi_sha1' => $sha1 ];
		if ( $timestamp ) {
			$conds['oi_timestamp'] = $dbr->timestamp( $timestamp );
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

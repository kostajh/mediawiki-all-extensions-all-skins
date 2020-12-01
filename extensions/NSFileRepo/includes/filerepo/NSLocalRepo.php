<?php

/**
 * Class definitions for NSFileRepo
 */

class NSLocalRepo extends LocalRepo {
	protected $fileFactory = array( 'NSLocalFile', 'newFromTitle' );
	protected $fileFactoryKey = array( 'NSLocalFile', 'newFromKey' );
	protected $oldFileFactory = array( 'NSOldLocalFile', 'newFromTitle' );
	protected $fileFromRowFactory = array( 'NSLocalFile', 'newFromRow' );
	protected $oldFileFromRowFactory = array( 'NSOldLocalFile', 'newFromRow' );
	protected $oldFileFactoryKey = array( 'NSOldLocalFile', 'newFromKey' );

	static function getHashPathForLevel( $name, $levels ) {
		global $wgContLang;
		$bits = explode( ':',$name );
		$filename = $bits[ count( $bits ) - 1 ];
		$path = parent::getHashPathForLevel( $filename, $levels );
		return count( $bits ) > 1 ?
			$wgContLang->getNsIndex( $bits[0] ) .'/'. $path : $path;
	}

	/**
	 * Get a relative path including trailing slash, e.g. f/fa/
	 * If the repo is not hashed, returns an empty string
	 * This is needed because self:: will call parent if not included - exact same as in FSRepo
	 */
	function getHashPath( $name ) {
		return self::getHashPathForLevel( $name, $this->hashLevels );
	}

	/**
	 * Pick a random name in the temp zone and store a file to it.
	 * @param string $originalName The base name of the file as specified
	 *     by the user. The file extension will be maintained.
	 * @param string $srcPath The current location of the file.
	 * @return Status object with the URL in the value.
	 */
	function storeTemp( $originalName, $srcPath ) {
		$date = gmdate( "YmdHis" );
		$hashPath = $this->getHashPath( $originalName );
		$filename = $this->getFileNameStripped( $originalName );
		$dstRel = "$hashPath$date!$filename";
		$dstUrlRel = $hashPath . $date . '!' . rawurlencode( $filename );
		$result = $this->store( $srcPath, 'temp', $dstRel );
		$result->value = $this->getVirtualUrl( 'temp' ) . '/' . $dstUrlRel;
		return $result;
	}

	function getFileNameStripped($suffix) {
		return(NSLocalFile::getFileNameStripped($suffix));
	}
}

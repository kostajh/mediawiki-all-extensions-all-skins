<?php

class NSFileRepoHelper {
	protected $pathRegEx = '#\\/(thumb\\/|archive\\/|deleted\\/)?(\d*?)?\\/[a-f0-9]{1}\\/[a-f0-9]{2}\\/(.*?)$#';

	/**
	 * Returns a Title object that can be used to check permissions against. ATTENTION: It will _not_ return a
	 * Title from NS_FILE, but either from NS_MAIN, or from the specific namespace that was found in the path!
	 * Examples for $path:
	 * /thumb/1502/7/78/Some_File.png/300px-Some_File.png
	 * /1502/7/78/Some_File.png
	 * @param $path
	 * @return null|Title
	 */
	public function getTitleFromPath( $path ) {
		$filename = wfBaseName( $path );
		if( UploadBase::isThumbName( $filename ) ) {
			//HINT: Thumbname-to-filename-conversion taken from includes/Upload/UploadBase.php
			//Check for filenames like 50px- or 180px-, these are mostly thumbnails
			$filename = substr( $filename , strpos( $filename , '-' ) +1 );
		}

		$title = Title::newFromText( $filename );

		$matches = array();
		preg_match( $this->pathRegEx , $path, $matches );
		if( empty( $matches[2] ) ) { //Not a file from a namespace?
			return $title;
		}

		$title = Title::makeTitleSafe( (int)$matches[2], $filename );
		return $title;
	}
}
<?php
class WebDAVNSFRFileFile extends WebDAVFileFile {
	protected $sName = '';
	protected $sPrefix = '';

	/**
	 *
	 * @param File $oFile
	 */
	public function __construct( $oFile ) {
		parent::__construct( $oFile );
		// 'File:Project:someFile.pdf' --> 'Project:someFile.pdf'
		$this->sName = $oFile->getTitle()->getText();
		$aFileTitleParts = explode( ':', $this->sName, 2 );
		if ( count( $aFileTitleParts ) > 1 ) {
			$this->sPrefix = str_replace( ' ', '_', $aFileTitleParts[0] ) . ':';
			$this->sName = $aFileTitleParts[1];
		}

		$this->sName = str_replace( ' ', '_', $this->sName );
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->sName;
	}

	/**
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$name = $this->sPrefix . $name;
		parent::setName( $name );
	}
}

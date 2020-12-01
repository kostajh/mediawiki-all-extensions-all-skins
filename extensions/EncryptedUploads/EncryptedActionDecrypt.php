<?php

namespace EncryptedUploads;

use Action;
use LocalFile;
use RepoGroup;
use TempFSFile;

class EncryptedActionDecrypt extends Action {

	private static $CHUNK_SIZE = 1024 * 1024;

	public function getName() {
		return 'decrypt';
	}

	/**
	 * @return bool
	 */
	public function show() {
		$this->getOutput()->setPageTitle( 'Downloading encrypted file..' );

		if ( !$this->getTitle() || $this->getTitle()->getNamespace() !== NS_FILE ) {
			$this->getOutput()->addHTML( 'Action is not supported for this type of page.' );
			return false;
		}

		if ( !$this->getRequest()->wasPosted() ) {
			$this->getOutput()->addHTML( 'Action does not provide any view mechanics.' );
			return false;
		}

		$secret = trim( $this->getRequest()->getText( 'secret' ) );
		if ( !$secret ) {
			$this->getOutput()->addHTML( 'Secret is required to proceed.' );
			return false;
		}

		wfDebugLog( 'EncryptedUploads', 'Received decryption request for a file..' );

		$title = $this->getTitle();
		$encryptor = EncryptedUploads::getInstance();
		$data = $encryptor->getEncrypted( $title->getArticleID() );

		if ( !$data ) {
			$this->getOutput()->addHTML( 'Selected file is not encrypted.' );
			return false;
		}

		wfDebugLog( 'EncryptedUploads', 'File is encrypted, proceeding..' );

		$file = LocalFile::newFromTitle( $title, RepoGroup::singleton()->getLocalRepo() );
		if ( !$file ) {
			$this->getOutput()->addHTML( 'Can not fetch file' );
			return false;
		}

		wfDebugLog( 'EncryptedUploads', 'Physical file location is ' . $file->getLocalRefPath() );
		wfDebugLog( 'EncryptedUploads', 'Allocating temporary file for decryption..' );

		$tmpFile = TempFSFile::factory( 'ENCRYPT', 'decrypt_', wfTempDir() );
		if ( $tmpFile === null ) {
			$this->getOutput()
				 ->addHTML( 'Unable to allocate temporary file, please contact system administrator' );
			return false;
		}

		wfDebugLog( 'EncryptedUploads', 'Temp file allocated at ' . $tmpFile->getPath() );

		$tmpFile->bind( $this );
		$tmpFile->preserve();

		wfDebugLog( 'EncryptedUploads', 'Decrypting..' );

		$result = $encryptor->decryptFile( $file->getLocalRefPath(), $tmpFile->getPath(), $secret );

		if ( !$result ) {
			$this->getOutput()
				 ->addHTML( 'An error occurred during file decryption. Probably provided ' .
							'secret is not correct one.' );
			return false;
		}

		wfDebugLog( 'EncryptedUploads', 'Sending file to browser..' );

		// Pass file contents to browser

		error_reporting( 0 );
		ob_start();

		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $tmpFile->getPath() );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: attachment; filename="'
				. basename( $file->getName() ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $tmpFile->getPath() ) );

		ob_clean();
		ob_end_flush();

		// Drop file contents to the browser
		$status = $this->readfile_chunked( $tmpFile->getPath() );

		wfDebugLog( 'EncryptedUploads', 'Done, bytes sent: ' . $status );

		// Prevent control to be returned to outer scripts
		exit;
	}

	/**
	 * Read a file and display its content chunk by chunk
	 *
	 * @param string $filename
	 * @param bool $retbytes
	 *
	 * @return bool|int
	 */
	private function readfile_chunked( $filename, $retbytes = true ) {
		$buffer = '';
		$cnt = 0;
		$handle = fopen( $filename, 'rb' );

		if ( $handle === false ) {
			return false;
		}

		while ( !feof( $handle ) ) {
			$buffer = fread( $handle, self::$CHUNK_SIZE );
			echo $buffer;
			ob_flush();
			flush();

			if ( $retbytes ) {
				$cnt += strlen( $buffer );
			}
		}

		$status = fclose( $handle );

		if ( $retbytes && $status ) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}

		return $status;
	}

}

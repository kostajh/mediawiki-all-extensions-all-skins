<?php

class NSFileRepoFSFileBackend extends FSFileBackend {

	/**
	 * Enables support for Non-ASCII filenames event on Windows. As we always deliver through a PHP script
	 * (e.g. img_auth.php) the encoding issue of PHP on the Windows FS is not a problem anymore.
	 * @see FSFileBackend::getFeatures()
	 * @return int
	 */
	public function getFeatures() {
		return FileBackend::ATTR_UNICODE_PATHS;
	}
}
<?php

class ImageCompareException extends Exception {
	/* New exception for ImageCompare.
	   $message: The key for message in internationalization file, without "imageCompare-".
	*/
	public function __construct($message) {
		parent::__construct($message);
	}
}
<?php

/**
 * Stream MP3 using HTML5 <audio> tag
 */
class MP3MediaHandler extends MediaHandler {

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return true
	 */
	public function validateParam( $name, $value ) {
		return true;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	public function makeParamString( $params ) {
		return '';
	}

	/**
	 * @param string $string
	 * @return array
	 */
	public function parseParamString( $string ) {
		return [];
	}

	/**
	 * @param File $file
	 * @param array &$params
	 * @return true
	 */
	public function normaliseParams( $file, &$params ) {
		return true;
	}

	/**
	 * @param File $file
	 * @param string $path
	 * @return false
	 */
	public function getImageSize( $file, $path ) {
		return false;
	}

	/**
	 * Prevent "no higher resolution" message.
	 *
	 * @param File $file
	 * @return true
	 */
	public function mustRender( $file ) {
		return true;
	}

	/**
	 * @return array
	 */
	public function getParamMap() {
		return [];
	}

	/**
	 * @param File $file
	 * @param string $dstPath
	 * @param string $dstUrl
	 * @param array $params
	 * @param int $flags
	 * @return MP3OutputRenderer
	 */
	public function doTransform( $file, $dstPath, $dstUrl, $params, $flags = 0 ) {
		return new MP3OutputRenderer( $file->getFullUrl(), $file->getTitle() );
	}
}

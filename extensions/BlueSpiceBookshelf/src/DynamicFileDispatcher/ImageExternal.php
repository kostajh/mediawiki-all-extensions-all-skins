<?php

namespace BlueSpice\Bookshelf\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\Module;

class ImageExternal extends \BlueSpice\DynamicFileDispatcher\File {

	/**
	 *
	 * @var string
	 */
	protected $src = '';

	/**
	 *
	 * @param Module $dfd
	 * @param sring $src
	 */
	public function __construct( Module $dfd, $src ) {
		parent::__construct( $dfd );
		$this->src = $src;
	}

	/**
	 * Sets the headers for given \WebResponse
	 * @param \WebResponse $response
	 * @return void
	 */
	public function setHeaders( \WebResponse $response ) {
		$response->header(
			"Location:$this->src",
			true
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		return '';
	}
}

<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\Module;
use MediaTransformError;
use ThumbnailImage;

class ImageExternal extends Image {

	/**
	 *
	 * @var string
	 */
	protected $src = '';

	/**
	 *
	 * @param Module $dfd
	 * @param ThumbnailImage|MediaTransformError|bool $thumb
	 * @param string $src
	 */
	public function __construct( Module $dfd, $thumb, $src ) {
		parent::__construct( $dfd, $thumb );
		$this->src = $src;
	}

	/**
	 * Sets the headers for given \WebResponse
	 * @param \WebResponse $response
	 * @return void
	 */
	public function setHeaders( \WebResponse $response ) {
		$this->dfd->getContext()->getRequest()->response()->header(
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

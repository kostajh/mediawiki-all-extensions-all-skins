<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\Module;
use MediaTransformError;
use ThumbnailImage;

class Image extends \BlueSpice\DynamicFileDispatcher\File {
	/**
	 *
	 * @var ThumbnailImage|MediaTransformError|bool
	 */
	protected $thumb = false;

	/**
	 *
	 * @param Module $dfd
	 * @param ThumbnailImage|MediaTransformError|bool $thumb
	 */
	public function __construct( Module $dfd, $thumb ) {
		parent::__construct( $dfd );
		$this->thumb = $thumb;
	}

	/**
	 * Sets the headers for given \WebResponse
	 * @param \WebResponse $response
	 * @return void
	 */
	public function setHeaders( \WebResponse $response ) {
		if ( $this->thumb instanceof ThumbnailImage ) {
			$headers = [];
			$headers[] = 'Cache-Control: private';
			$headers[] = 'Vary: Cookie';

			$this->thumb->streamFile( $headers );
		} elseif ( $this->thumb instanceof \MediaTransformError ) {
			$response->statusHeader( $this->thumb->getHttpStatusCode() );
		} else {
			$response->statusHeader( 404 );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		if ( $this->thumb instanceof ThumbnailImage ) {
			return $this->thumb->getFile()->getMimeType();
		}
		return '';
	}
}

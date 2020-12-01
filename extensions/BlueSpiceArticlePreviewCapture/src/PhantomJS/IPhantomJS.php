<?php

namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

interface IPhantomJS {

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies );
}

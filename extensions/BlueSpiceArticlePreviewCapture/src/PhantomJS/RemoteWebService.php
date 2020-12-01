<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\MediaWikiServices;

class RemoteWebService implements IPhantomJS {

	/**
	 * PhantomJS backend endpoint
	 * @var string
	 */
	private $phantomJSServiceURL;

	/**
	 * @return RemoteWebService
	 * @throws \ConfigException
	 */
	public static function factory() {
		$phantomJSServiceURL = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSServiceURL' );

		return new self( $phantomJSServiceURL );
	}

	/**
	 * RemoteWebService constructor.
	 * @param string $phantomJSServiceURL
	 */
	public function __construct( $phantomJSServiceURL ) {
		$this->phantomJSServiceURL = $phantomJSServiceURL;
	}

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$es = htmlspecialchars_decode( $url );
		$queryParams = [
			'url' => $baseUrl . $es,
			'cookies' => $cookies
		];

		$requestUrl = $this->phantomJSServiceURL . '/?' . http_build_query( $queryParams );

		$fileData = \Http::get( $requestUrl, [ 'sslVerifyHost' => false, 'sslVerifyCert' => false ] );

		return $fileData;
	}
}

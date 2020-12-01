<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\MediaWikiServices;

class NativeShellExec implements IPhantomJS {

	/**
	 * phantomjs local path
	 * @var string
	 */
	private $phantomJSExe;
	private $phantomJSOptions;
	private $captureFile;
	private $escapeShellCommand;
	private $scriptPath;

	/**
	 * @return NativeShellExec
	 * @throws \ConfigException
	 */
	public static function factory() {
		$phantomJSExe = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSExecutable' );

		$phantomJSOptions = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSOptions' );

		$captureFile = $GLOBALS[ 'wgExtensionDirectory' ]
			. "/BlueSpiceArticlePreviewCapture/webservices/render.js";

		$escapeShellCommand = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSEscapeShellCommand' );

		$scriptPath = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ScriptPath' );

		return new static( $phantomJSExe, $phantomJSOptions, $captureFile,
			$escapeShellCommand, $scriptPath );
	}

	/**
	 * NativeShellExec constructor.
	 * @param string $phantomJSExe
	 * @param string $phantomJSOptions
	 * @param string $captureFile
	 * @param string $escapeShellCommand
	 * @param string $scriptPath
	 */
	public function __construct( $phantomJSExe, $phantomJSOptions, $captureFile,
								 $escapeShellCommand, $scriptPath ) {
		$this->phantomJSExe = $phantomJSExe;
		$this->phantomJSOptions = $phantomJSOptions;
		$this->captureFile = $captureFile;
		$this->escapeShellCommand = $escapeShellCommand;
		$this->scriptPath = $scriptPath;
	}

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );
		$escapedCommand = [];
		$i = 0;
		foreach ( $command as $commandPart ) {
			$i++;
			if ( $i === 1 && !$this->escapeShellCommand ) {
				$escapedCommand[] = $commandPart;
				continue;
			}
			$escapedCommand[] = escapeshellarg( $commandPart );
		}
		$out = [];
		$cmd = implode( ' ',  $escapedCommand );

		// wfShellExec will fail due to memory limits
		if ( wfIsWindows() ) {
			exec( $cmd, $out );
			$base64Data = implode( "\n", $out );
		} else {
			$base64Data = exec( $cmd );
		}

		return base64_decode( $base64Data );
	}

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return array
	 */
	protected function getCommandArguments( $url, $baseUrl, $cookies ) {
		$parsedBaseUrl = wfParseUrl( $baseUrl );

		$cookieDomain = $parsedBaseUrl[ 'host' ];
		$cookiePath = $this->scriptPath;

		$command = [];
		$command[] = $this->phantomJSExe;
		foreach ( $this->phantomJSOptions as $option ) {
			$command[] = $option;
		}

		$command = array_merge(
			$command,
			[ $this->captureFile, $baseUrl . $url, $cookies, $cookieDomain, $cookiePath ]
		);

		return $command;
	}
}

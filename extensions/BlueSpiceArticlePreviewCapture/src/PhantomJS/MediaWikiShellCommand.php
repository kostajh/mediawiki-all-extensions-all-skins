<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\Shell\Shell;

class MediaWikiShellCommand extends NativeShellExec {

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );

		$output = Shell::command( $command )
			->restrict( Shell::RESTRICT_DEFAULT | Shell::NO_NETWORK )
			->execute()->getStdout();

		return base64_decode( $output );
	}
}

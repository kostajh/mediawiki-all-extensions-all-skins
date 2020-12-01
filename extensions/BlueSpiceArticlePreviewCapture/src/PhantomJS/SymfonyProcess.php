<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use Symfony\Component\Process\ProcessBuilder;

class SymfonyProcess extends NativeShellExec {

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );

		$builder = new ProcessBuilder( $command );
		$process = $builder->getProcess();

		$output = '';
		$process->run( function ( $type, $capturedOutput ) use ( &$output ) {
			$output .= $capturedOutput;
		} );

		return base64_decode( $output );
	}
}

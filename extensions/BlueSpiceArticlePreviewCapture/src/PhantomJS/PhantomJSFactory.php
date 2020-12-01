<?php

namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MWException;

class PhantomJSFactory {

	private $phantomJSBackendRegistry;
	private $phantomJSBackend;

	/**
	 * PhantomJSFactory constructor.
	 * @param ExtensionAttributeBasedRegistry $phantomJSBackendRegistry
	 * @param string $phantomJSBackend
	 */
	public function __construct( ExtensionAttributeBasedRegistry $phantomJSBackendRegistry,
								 $phantomJSBackend ) {
		$this->phantomJSBackendRegistry = $phantomJSBackendRegistry->getAllValues();
		$this->phantomJSBackend = $phantomJSBackend;
	}

	/**
	 * @return IPhantomJS
	 * @throws MWException
	 */
	public function getPhantomJS() {
		if ( array_key_exists( $this->phantomJSBackend, $this->phantomJSBackendRegistry ) ) {
			$phantomJSBackend = call_user_func( $this->phantomJSBackendRegistry[ $this->phantomJSBackend ] );
			if ( $phantomJSBackend instanceof IPhantomJS ) {
				return $phantomJSBackend;
			}
		}

		throw new MWException(
			"There is no " . $this->phantomJSBackend . " phantomjs backend in registry. 
			Please, check your extension.json" );
	}
}

<?php

namespace MediaWiki\Extension\ExtJSBase\ResourceModule;

class Classes extends Base {

	const NAMESPACE_ROOT = 'extjsNamespaceRoot';

	protected $subNamespaceSubfolder = '';

	/**
	 *
	 * @param array $options
	 * @param string|null $localBasePath
	 * @param string|null $remoteBasePath
	 * @throws \Exception
	 */
	public function __construct( $options = [], $localBasePath = null, $remoteBasePath = null ) {
		parent::__construct( $options, $localBasePath, $remoteBasePath );

		if ( !isset( $options[self::NAMESPACE_ROOT] ) ) {
			throw new \Exception( "No value for '" . self::NAMESPACE_ROOT . "' provided!" );
		}
		$this->subNamespaceSubfolder = $options[self::NAMESPACE_ROOT];
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	public function getStyleFiles( \ResourceLoaderContext $context ) {
		return [
			$this->findFiles( [ 'css', 'less' ] )
		];
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	protected function getScriptFiles( \ResourceLoaderContext $context ) {
		return $this->findFiles( [ 'js' ] );
	}

	/**
	 *
	 * @param array $fileExtensions
	 * @return array
	 */
	protected function findFiles( $fileExtensions ) {
		$path = "{$this->localBasePath}/{$this->subNamespaceSubfolder}";
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$path,
				\RecursiveDirectoryIterator::SKIP_DOTS
			),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		$foundFiles = [];
		foreach ( $files as $name => $fileInfo ) {
			$foundFiles[] = str_replace( $this->localBasePath, '', $name );
		}

		return $foundFiles;
	}
}

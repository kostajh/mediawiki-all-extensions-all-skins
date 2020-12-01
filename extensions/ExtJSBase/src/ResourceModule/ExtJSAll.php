<?php

namespace MediaWiki\Extension\ExtJSBase\ResourceModule;

use MediaWiki\Extension\ExtJSBase;

class ExtJSAll extends Base {

	/**
	 *
	 * @var ExtJSBase\ITheme
	 */
	protected $extjsTheme = null;

	/**
	 *
	 * @param array $options
	 * @param string|null $localBasePath
	 * @param string|null $remoteBasePath
	 */
	public function __construct( $options = [], $localBasePath = null, $remoteBasePath = null ) {
		parent::__construct( $options, $localBasePath, $remoteBasePath );
		$config = \MediaWiki\MediaWikiServices::getInstance()
				->getConfigFactory()->makeConfig( 'extjsbase' );
		$themeClass = $config->get( ExtJSBase\Config::THEME );
		$this->extjsTheme = new $themeClass();
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	public function getStyleFiles( \ResourceLoaderContext $context ) {
		return [
			$this->extjsTheme->getStyleFiles()
		];
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	protected function getScriptFiles( \ResourceLoaderContext $context ) {
		$this->scripts = array_merge(
			$this->getExtJSScriptFiles( $context ),
			$this->extjsTheme->getScriptFiles(),
			$this->getOverrideScriptFiles()
		);
		$files = parent::getScriptFiles( $context );
		return $files;
	}

	/**
	 *
	 * @return array
	 */
	public function getTargets() {
		return [ "mobile", "desktop" ];
	}

	/**
	 * @return string[]
	 */
	protected function getOverrideScriptFiles() {
		$overrideBaseDir = "{$this->localBasePath}/MWExt/overrides/";
		$files = [];
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$overrideBaseDir,
				\FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
			)
		);

		/** @var \SplFileInfo $fileinfo */
		foreach ( $iterator as $pathname => $fileinfo ) {
			if ( $fileinfo->isDir() ) {
				continue;
			}

			$files[] = str_replace( $this->localBasePath, '', $pathname );
		}

		return $files;
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return string[]
	 */
	protected function getExtJSScriptFiles( $context ) {
		$files = [];
		if ( $context->getDebug() ) {
			$files[] = 'extjs/ext-all-debug.js';
		} else {
			$files[] = 'extjs/ext-all.js';
		}

		$files[] = 'ext.extjsbase.init.js';

		return $files;
	}

}

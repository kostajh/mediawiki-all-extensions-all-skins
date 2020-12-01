<?php

namespace MediaWiki\Extension\ExtJSBase\ResourceModule;

use Language;
use OutputPage;
use ResourceLoaderContext;

/**
 * This class basically contains copies of some methods from the base class that have been changed
 * from 'protected' to 'private' in https://gerrit.wikimedia.org/r/#/c/mediawiki/core/+/442008/
 * It needs to be kept in sync with the base class in future releases.
 * Even better: The custom RL classes in this extension should not build on
 * \ResourceLoaderFileModule but on \ResourceLoaderModule
 */
class Base extends \ResourceLoaderFileModule {

	/**
	 * Gets all scripts for a given context concatenated together.
	 *
	 * @param ResourceLoaderContext $context Context in which to generate script
	 * @return string JavaScript code for $context
	 */
	public function getScript( ResourceLoaderContext $context ) {
		$files = $this->getScriptFiles( $context );
		return $this->getDeprecationInformation( $context ) . $this->readScriptFiles( $files );
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	public function getScriptURLsForDebug( ResourceLoaderContext $context ) {
		$urls = [];
		foreach ( $this->getScriptFiles( $context ) as $file ) {
			$urls[] = OutputPage::transformResourcePath(
				$this->getConfig(),
				$this->getRemotePath( $file )
			);
		}
		return $urls;
	}

	/**
	 * Get a list of script file paths for this module, in order of proper execution.
	 *
	 * @param ResourceLoaderContext $context
	 * @return array List of file paths
	 */
	protected function getScriptFiles( ResourceLoaderContext $context ) {
		$files = array_merge(
			$this->scripts,
			$this->getLanguageScripts( $context->getLanguage() ),
			self::tryForKey( $this->skinScripts, $context->getSkin(), 'default' )
		);
		if ( $context->getDebug() ) {
			$files = array_merge( $files, $this->debugScripts );
		}

		return array_unique( $files, SORT_REGULAR );
	}

	/**
	 * Get the contents of a list of JavaScript files. Helper for getScript().
	 *
	 * @param array $scripts List of file paths to scripts to read, remap and concetenate
	 * @return string Concatenated and remapped JavaScript data from $scripts
	 * @throws MWException
	 */
	private function readScriptFiles( array $scripts ) {
		if ( empty( $scripts ) ) {
			return '';
		}
		$js = '';
		foreach ( array_unique( $scripts, SORT_REGULAR ) as $fileName ) {
			$localPath = $this->getLocalPath( $fileName );
			if ( !file_exists( $localPath ) ) {
				throw new MWException( __METHOD__ . ": script file not found: \"$localPath\"" );
			}
			$contents = $this->stripBom( file_get_contents( $localPath ) );
			$js .= $contents . "\n";
		}
		return $js;
	}

	/**
	 * Get the set of language scripts for the given language,
	 * possibly using a fallback language.
	 *
	 * @param string $lang
	 * @return array
	 */
	private function getLanguageScripts( $lang ) {
		$scripts = self::tryForKey( $this->languageScripts, $lang );
		if ( $scripts ) {
			return $scripts;
		}
		$fallbacks = Language::getFallbacksFor( $lang );
		foreach ( $fallbacks as $lang ) {
			$scripts = self::tryForKey( $this->languageScripts, $lang );
			if ( $scripts ) {
				return $scripts;
			}
		}

		return [];
	}

}

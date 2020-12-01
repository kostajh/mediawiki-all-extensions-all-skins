<?php

namespace MediaWiki\Extension\ExtJSBase\Theme;

use MediaWiki\Extension\ExtJSBase\ITheme;

class MediaWikiTouch implements ITheme {

	/**
	 *
	 * @var string
	 */
	protected $basePath = 'mediawiki.extjs/theme-mediawiki-touch';

	/**
	 *
	 * @return string[]
	 */
	public function getStyleFiles() {
		return [
			$this->makePath( 'theme-mediawiki-touch-all_1.css' ),
			$this->makePath( 'theme-mediawiki-touch-all_2.css' ),
			$this->makePath( 'theme-mediawiki-touch.postbuild.less' ),
		];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getScriptFiles() {
		return [
			$this->makePath( 'theme-mediawiki-touch.js' )
		];
	}

	/**
	 *
	 * @param string $fileName
	 * @return string
	 */
	protected function makePath( $fileName ) {
		return "{$this->basePath}/$fileName";
	}
}

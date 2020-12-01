<?php

namespace MediaWiki\Extension\ExtJSBase;

interface ITheme {

	/**
	 * @return string[]
	 */
	public function getStyleFiles();

	/**
	 * @return string[]
	 */
	public function getScriptFiles();
}

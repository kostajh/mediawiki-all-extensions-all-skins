<?php

namespace MediaWiki\Extension\ExtJSBase\ResourceModule;

class ExtJSUX extends Base {

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	public function getStyleFiles( \ResourceLoaderContext $context ) {
		$cssFile = 'extjs/packages/ux/';
		if ( $context->getDebug() ) {
			$cssFile .= 'ux-all-debug.css';
		} else {
			$cssFile .= 'ux-all.css';
		}

		$this->styles = [ $cssFile ];
		return parent::getStyleFiles( $context );
	}

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	protected function getScriptFiles( \ResourceLoaderContext $context ) {
		$jsFile = 'extjs/packages/ux/';
		if ( $context->getDebug() ) {
			$jsFile .= 'ux-debug.js';
		} else {
			$jsFile .= 'ux.js';
		}

		$this->scripts = [ $jsFile ];
		$files = parent::getScriptFiles( $context );
		return $files;
	}
}

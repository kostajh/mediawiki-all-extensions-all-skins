<?php

namespace MediaWiki\Extension\ExtJSBase\ResourceModule;

class ExtJSCharts extends Base {

	/**
	 *
	 * @param \ResourceLoaderContext $context
	 * @return array
	 */
	public function getStyleFiles( \ResourceLoaderContext $context ) {
		$cssFile = 'extjs/packages/charts/';
		if ( $context->getDebug() ) {
			$cssFile .= 'charts-all-debug.css';
		} else {
			$cssFile .= 'charts-all.css';
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
		$jsFile = 'extjs/packages/charts/';
		if ( $context->getDebug() ) {
			$jsFile .= 'charts-debug.js';
		} else {
			$jsFile .= 'charts.js';
		}

		$this->scripts = [ $jsFile ];
		$files = parent::getScriptFiles( $context );
		return $files;
	}
}

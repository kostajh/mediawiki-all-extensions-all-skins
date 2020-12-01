<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MediaWiki\Extension\FlexiSkin\PluginBase;

class FreeCss extends PluginBase {
	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/plugin/FreeCss.js'
		];
	}

	/**
	 * @return array with css files
	 */
	public function getCSSFiles() {
		return [
			'all' => []
		];
	}

	/**
	 * @return string Name of the module
	 */
	public function getPluginName() {
		return 'flexiskin.ui.plugin.FreeCss';
	}

	/**
	 * @return array valid skinnames
	 */
	public function getValidSkins() {
		return [
			'bluespicecalumma'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function setDefaults( IFlexiSkin $skin, &$config ) {
		// No defaults
	}
}

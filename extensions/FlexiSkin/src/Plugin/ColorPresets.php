<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use MediaWiki\Extension\FlexiSkin\PluginBase;

class ColorPresets extends PluginBase {
	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/ColorPalette.js',
			'js/ui/ColorPresetOption.js',
			'js/ui/ColorPresetWidget.js',
			'js/ui/plugin/ColorPresets.js'
		];
	}

	/**
	 * @return array with css files
	 */
	public function getCSSFiles() {
		return [
			'all' => [
				'stylesheets/plugin/ColorPresets.css',
			]
		];
	}

	/**
	 * @return string Name of the module
	 */
	public function getPluginName() {
		return 'flexiskin.ui.plugin.ColorsPresets';
	}

	/**
	 * @return array valid skinnames
	 */
	public function getValidSkins() {
		return [
			'bluespicecalumma'
		];
	}
}

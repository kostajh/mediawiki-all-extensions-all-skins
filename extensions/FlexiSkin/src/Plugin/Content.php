<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use MediaWiki\Extension\FlexiSkin\PluginBase;

class Content extends PluginBase {
	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/plugin/Content.js'
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
		return 'flexiskin.ui.plugin.Content';
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
	public function getLessVarsMap(): array {
		return [
			'content-bg' => 'content/content_colors/background',
			'content-fg' => 'content/content_colors/foreground',
			'link-fg' => 'content/content_colors/primary_link',
			'new-link-fg' => 'content/content_colors/new_link',
			'content-font-size' => 'content/font/font_size',
			'content-font-weight' => 'content/font/weight',
			'content-primary-font-family' => 'content/font/family',
			'content-width' => 'content/layout/width',
			'content-h1-fg' => 'content/header/h1/color',
			'content-h1-font-size' => 'content/header/h1/font_size',
			'content-h1-border' => 'content/header/h1/border',
			'content-h2-fg' => 'content/header/h2/color',
			'content-h2-font-size' => 'content/header/h2/font_size',
			'content-h2-border' => 'content/header/h2/border',
			'content-h3-fg' => 'content/header/h3/color',
			'content-h3-font-size' => 'content/header/h3/font_size',
			'content-h3-border' => 'content/header/h3/border',
			'content-h4-fg' => 'content/header/h4/color',
			'content-h4-font-size' => 'content/header/h4/font_size',
			'content-h4-border' => 'content/header/h4/border',
			'content-h5-fg' => 'content/header/h5/color',
			'content-h5-font-size' => 'content/header/h5/font_size',
			'content-h5-border' => 'content/header/h5/border',
			'content-h6-fg' => 'content/header/h6/color',
			'content-h6-font-size' => 'content/header/h6/font_size',
			'content-h6-border' => 'content/header/h6/border',
		];
	}
}

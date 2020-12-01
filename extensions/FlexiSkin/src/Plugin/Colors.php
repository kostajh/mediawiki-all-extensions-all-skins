<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MediaWiki\Extension\FlexiSkin\PluginBase;

class Colors extends PluginBase {
	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/plugin/Colors.js'
		];
	}

	/**
	 * @return array with css files
	 */
	public function getCSSFiles() {
		return [
			'all' => [
				'stylesheets/plugin/Colors.css',
			]
		];
	}

	/**
	 * @return string Name of the module
	 */
	public function getPluginName() {
		return 'flexiskin.ui.plugin.Colors';
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
			'primary-bg' => 'colors/background/primary',
			'secondary-bg' => 'colors/background/secondary',
			'tertiary-bg' => 'colors/background/tertiary',
			'quaternary-bg' => 'colors/background/quaternary',
			'body-bg' => 'colors/background/body',
			'primary-fg' => 'colors/foreground/primary',
			'secondary-fg' => 'colors/foreground/secondary',
			'tertiary-fg' => 'colors/foreground/tertiary',
			'quaternary-fg' => 'colors/foreground/quaternary',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function setDefaults( IFlexiSkin $skin, &$config ) {
		// No defaults for colors - we use themes
	}
}

<?php

namespace MediaWiki\Extension\FlexiSkin;

interface IPlugin {

	/**
	 * return array with js files
	 */
	public function getJSFiles();

	 /**
	  * return array with css files
	  */
	public function getCSSFiles();

	/**
	 * @return string Name of the plugin
	 */
	public function getPluginName();

	/**
	 * @return array valid skinnames
	 */
	public function getValidSkins();

	/**
	 * @param IFlexiSkin $skin
	 * @return array
	 */
	public function getLessVars( IFlexiSkin $skin ): array;

	/**
	 * Get less var name => path is config map
	 * Config path should use / as delimiter (key1/key2/key3)
	 *
	 * @return array
	 */
	public function getLessVarsMap(): array;

	/**
	 * Adapt configuration values before loading skin config
	 * Called when FlexiSkin is being initialized
	 *
	 * @param array &$config
	 * @return void
	 */
	public function adaptConfiguration( &$config );

	/**
	 * Set the default values of inputs if nothing is customized
	 * By default, called from self::adaptConfiguration()
	 *
	 * @param IFlexiSkin $skin
	 * @param array &$config
	 * @return void
	 */
	public function setDefaults( IFlexiSkin $skin, &$config );
}

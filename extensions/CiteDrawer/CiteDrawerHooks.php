<?php

/**
 * Hooks for CiteDrawer extension
 *
 * @file
 * @ingroup Extensions
 */
class CiteDrawerHooks {

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.citedrawer.main' );
	}

	/**
	 * @param array &$vars
	 * @param OutputPage $out
	 */
	public static function onMakeGlobalVariablesScript( &$vars, $out ) {
		$vars['wgCiteDrawerEnableDesktop'] = $out->getConfig()->get( 'CiteDrawerEnableDesktop' );
		$vars['wgCiteDrawerEnableMobile'] = $out->getConfig()->get( 'CiteDrawerEnableMobile' );
		$vars['wgCiteDrawerTheme'] = $out->getConfig()->get( 'CiteDrawerTheme' );
	}

}

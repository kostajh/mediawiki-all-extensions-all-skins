<?php

class PagePopupsHooks {
	/**
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		# This damages page style:
		# global $wgScriptPath;
		# $out->addScriptFile( "$wgScriptPath/extensions/PagePopups/PagePopups.js" );
		# $out->addScriptFile( "//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js" );
		# $out->addStyle( "//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" );
		# $out->addModules( 'jquery' ); // loaded automatically
		# $out->addModules( array('jquery.ui.dialog', 'ext.PagePopups.popup') );
		$out->addModules( [ 'ext.PagePopups.popup' ] );

		return true; // TODO: needed?
	}
}

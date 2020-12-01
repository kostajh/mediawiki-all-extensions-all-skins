<?php

class OOJsUIAjaxLoginHooks {
	/**
	 * BeforePageDisplay hook handler. Add the required module for this extension.
	 *
	 * @param OutputPage &$out
	 * @param Skin &$sk
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$sk ) {
		if ( !$out->getUser()->isLoggedIn() ) {
			$out->addModules( [
					'ext.OOJsUIAjaxLogin.init'
				]
			);
		}
	}
}

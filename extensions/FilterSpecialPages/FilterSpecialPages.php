<?php

class FilterSpecialPages {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, $skin ) {
		if ( $out->getTitle()->isSpecial( 'Specialpages' ) ) {
			$out->addModules( 'ext.filterspecialpages' );
		}
	}

}

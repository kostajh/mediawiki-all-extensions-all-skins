<?php

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 *
 * @ingroup Skins
 */
class SkinDeskMessMirrored extends SkinTemplate {
	public $skinname = 'deskmessmirrored', $stylename = 'deskmessmirrored',
		$template = 'DeskMessMirroredTemplate';

	/**
	 * Add some required CSS classes to the <body> element for pages rendered with
	 * this skin.
	 *
	 * @param OutputPage $out
	 * @param array &$bodyAttrs Pre-existing attributes of the <body> tag
	 */
	public function addToBodyAttributes( $out, &$bodyAttrs ) {
		$bodyAttrs['class'] .= ' home blog custom-background';
	}

	/**
	 * @param OutputPage $out
	 */
	public function setupSkinUserCss( OutputPage $out ) {
		global $wgStylePath;

		parent::setupSkinUserCss( $out );

		// Add CSS via ResourceLoader
		$out->addModuleStyles( [
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.deskmessmirrored'
		] );

		// Internet Explorer fixes (required for IE11 at least)
		$out->addStyle( "{$wgStylePath}/DeskMessMirrored/css/ie.css", 'screen', 'IE' );
	}
}

<?php
/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 *
 * @ingroup Skins
 */

class SkinDusk extends SkinTemplate {
	public $skinname = 'dusk', $stylename = 'dusk',
		$template = 'DuskTemplate';

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Add CSS via ResourceLoader
		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.dusk'
		) );
	}
}
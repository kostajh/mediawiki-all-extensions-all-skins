<?php

class SkinAmethyst extends SkinTemplate {
	public $skinname = 'amethyst',
		$stylename = 'Amethyst',
		$template = 'AmethystTemplate',
		$useHeadElement = true;

	/**
	 * Initialize page, load JS modules via ResourceLoader
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );
		$out->addModules( 'skins.amethyst' );
	}

	/**
	 * Load CSS modules via ResourceLoader
	 *
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		$out->addModuleStyles( [
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.amethyst.styles'
		] );
	}
}

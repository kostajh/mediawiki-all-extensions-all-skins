<?php
/**
 * SkinTemplate class for the WoOgLeShades skin
 *
 * @ingroup Skins
 */
class SkinWoOgLeShades extends SkinTemplate {
	public $skinname = 'woogleshades',
		$stylename = 'WoOgLeShades',
		$template = 'WoOgLeShadesTemplate';

	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param $out OutputPage
	 */
	public function initPage( OutputPage $out ) {
		$out->addMeta( 'viewport',
			'width=device-width, initial-scale=1.0, ' .
			'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
		);
		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			'skins.woogleshades'
		] );
		$out->addModules( [
			'skins.woogleshades.js'
		] );

		// TODO Better source, actual font stack sucks.
		$out->addStyle( 'https://fonts.googleapis.com/css?family=Noto+Sans:400,400i,700,700i', 'screen' );
		$out->addStyle( 'https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,700i', 'screen' );
	}

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
	}
}

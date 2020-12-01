<?php

namespace BlueSpice\Calumma;

use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class Setup {
	/**
	 *
	 */
	public static function onCallback() {
		$GLOBALS[ 'egChameleonLayoutFile' ] = dirname( __DIR__ ) . '/layouts/default.xml';

		$GLOBALS[ 'wgUseMediaWikiUIEverywhere' ] = true;

		$GLOBALS[ 'wgVisualEditorSupportedSkins' ][] = 'bluespicecalumma';
		$GLOBALS[ 'wgVisualEditorSkinToolbarScrollOffset' ][ 'bluespicecalumma' ] = 64;

		// force overwrite of the custom header registry even when the skin is
		// loaded before the BlueSpiceCustomMenu extension. ERM:16808
		$GLOBALS[ 'bsgExtensionAttributeRegistryOverrides' ]["BlueSpiceCustomMenuRegistry"] = [
			"merge" => [
				"header" => "\\BlueSpice\\Calumma\\CustomMenu\\Header::getInstance"
			]
		];

		self::setLessVars();
	}

	/**
	 *
	 */
	private static function setLessVars() {
		$lessVars = LessVars::getInstance();

		$lessVars->setVar( 'primary-bg', '#3E5389' );
		$lessVars->setVar( 'primary-fg', 'white' );

		$lessVars->setVar( 'secondary-bg', 'white' );
		$lessVars->setVar( 'secondary-fg', '#666666' );

		$lessVars->setVar( 'tertiary-bg', '#D9D9D9' );
		$lessVars->setVar( 'tertiary-fg', '#999999' );

		$lessVars->setVar( 'quaternary-bg', '#999999' );
		$lessVars->setVar( 'quaternary-fg', 'white' );

		$lessVars->setVar( 'link-fg', '#0060DF' );
		$lessVars->setVar( 'new-link-fg', '#B73A3A' );

		$lessVars->setVar( 'font-weight-light', '300' );
		$lessVars->setVar( 'font-weight-regular', '400' );
		$lessVars->setVar( 'font-weight-medium', '500' );
		$lessVars->setVar( 'font-weight-bold', '700' );

		$lessVars->setVar( 'body-bg', 'white' );
		$lessVars->setVar( 'content-width', '61.25rem' );

		$lessVars->setVar( 'content-bg', 'white' );
		$lessVars->setVar( 'content-fg', '#333333' );
		$lessVars->setVar( 'content-font-size', '0.9375rem' );
		$lessVars->setVar( 'content-font-weight', '@font-weight-regular' );
		$lessVars->setVar( 'content-primary-font-family', '"Open sans"' );
		$lessVars->setVar( 'content-font-family', '@content-primary-font-family, "Roboto", "arial", "sans-serif"' );

		$lessVars->setVar( 'content-h1-fg', '#333333' );
		$lessVars->setVar( 'content-h1-font-size', '2rem' );
		$lessVars->setVar( 'content-h1-font-weight', '@font-weight-medium' );
		$lessVars->setVar( 'content-h1-border', 'none' );

		$lessVars->setVar( 'content-h2-fg', '#333333' );
		$lessVars->setVar( 'content-h2-font-size', '1.8rem' );
		$lessVars->setVar( 'content-h2-font-weight', '@font-weight-medium' );
		$lessVars->setVar( 'content-h2-border', '1px solid currentColor' );

		$lessVars->setVar( 'content-h3-fg', '#333333' );
		$lessVars->setVar( 'content-h3-font-size', '1.6rem' );
		$lessVars->setVar( 'content-h3-font-weight', '@font-weight-medium' );
		$lessVars->setVar( 'content-h3-border', 'none' );

		$lessVars->setVar( 'content-h4-fg', '#333333' );
		$lessVars->setVar( 'content-h4-font-size', '1.4rem' );
		$lessVars->setVar( 'content-h4-font-weight', '@font-weight-bold' );
		$lessVars->setVar( 'content-h4-border', 'none' );

		$lessVars->setVar( 'content-h5-fg', '#333333' );
		$lessVars->setVar( 'content-h5-font-size', '1.25rem' );
		$lessVars->setVar( 'content-h5-font-weight', '@font-weight-bold' );
		$lessVars->setVar( 'content-h5-border', 'none' );

		$lessVars->setVar( 'content-h6-fg', '#333333' );
		$lessVars->setVar( 'content-h6-font-size', '1.0625rem' );
		$lessVars->setVar( 'content-h6-font-weight', '@font-weight-bold' );
		$lessVars->setVar( 'content-h6-border', 'none' );
	}
}

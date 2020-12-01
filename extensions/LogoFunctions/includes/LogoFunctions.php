<?php

use MediaWiki\MediaWikiServices;

class LogoFunctions {
	/**
	 * Register our parser functions
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'setlogo', [ __CLASS__, 'renderSetLogo' ] );
		$parser->setFunctionHook( 'stamplogo', [ __CLASS__, 'renderStampLogo' ] );
	}

	/**
	 * Apply module for namespace logos (but only bother if in said namespaces)
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		$logos = RequestContext::getMain()->getConfig()->get( 'NamespaceLogos' );
		$namespace = $skin->getTitle()->getNamespace();

		if ( is_array( $logos ) && isset( $logos[$namespace] ) ) {
			$out->addModuleStyles( 'ext.logofunctions' );
		}
	}

	/**
	 * Take a file from user input and smash it onto the page as a logo. If they've got a
	 * .mw-wiki-logo, we're using it. NO MATTER WHAT, BECAUSE WE'RE BARBARIANS.
	 *
	 * But seriously, we can't do anything elegant here because of how mw uses RL to generate
	 * the logos in core, but frankly this works anyway in like 90% of skins we're likely to
	 * care about, so whatever.
	 *
	 * @param Parser $parser
	 * @param string $logo Name of an uploaded file
	 * @param int $width
	 */
	public static function renderSetLogo( $parser, $logo = '', $width = 0 ) {
		$css = self::getBackground( $logo, $width );
		if ( !$css ) {
			return Html::element( 'strong', [ 'class' => 'error' ],
				wfMessage( 'logofunctions-filenotexist', $logo )->inContentLanguage()->text()
			);
		}

		$css = 'body.mediawiki .mw-wiki-logo { ' . $css . '}';

		// wtf timeless
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Timeless' ) ) {
			$css = 'body.mediawiki .mw-wiki-logo.timeless-logo, ' . $css;
		}

		$output = $parser->getOutput();
		// Smash it onto the page!
		$output->addHeadItem( "<style>$css</style>", 'logooverride' );

		// But we do also have an actual module for any common logo override styles,
		// skin-specific stuff, whatever. We're not *complete* barbarians, here.
		$output->addModuleStyles( 'ext.logofunctions' );
	}

	/**
	 * Take a file from user input and smash it onto the page on top of a logo, same barbaric
	 * approach, but with added pseudoelements.
	 *
	 * TODO: localise top/bottom? Maybe, maybe not?
	 *
	 * @param Parser $parser
	 * @param string $logo Name of an uploaded file
	 * @param int $width (in pixels)
	 * @param string $placement 'top' or 'bottom', we can have one of each per page
	 * @param int $offsetX
	 * @param int $offsetY
	 */
	public static function renderStampLogo( $parser, $logo = '', $width = 0,
		$placement = 'top', $offsetX = 0, $offsetY = 0 ) {
		$background = self::getBackground( $logo, $width, true );
		if ( !$background ) {
			return Html::element( 'strong', [ 'class' => 'error' ],
				wfMessage( 'logofunctions-filenotexist', $logo )->inContentLanguage()->text()
			);
		}

		// wrapper
		$cssOuter = '.mw-wiki-logo { position: relative; }';

		// inner junk; arguably these first few things could be moved to the module
		// but aaaagh whatever, the rest definitely can't
		$css = "position: absolute; display: block; content: '';";
		$css .= $background;

		if ( $placement == 'bottom' ) {
			$selector = ':after';
		} else {
			$selector = ':before';
			// 'input handling' lololol
			$placement = 'top';
		}
		// Make sure these are probably valid css measurements, or at least won't break anything
		$regex = '/^-?\d*\.?\d+(?:[a-zA-Z]{2}|%)$/';
		$offsetX = preg_match( $regex, $offsetX ) ? $offsetX : 0;
		$offsetY = preg_match( $regex, $offsetY ) ? $offsetY : 0;
		$css .= " left: $offsetX; $placement: $offsetY;";

		$css = ".mw-wiki-logo{$selector} { $css }";

		$output = $parser->getOutput();
		// Smash it onto the page!
		$output->addHeadItem( "<style>$cssOuter\n$css</style>", "stamplogo-$placement" );
		// Common logo override styles, skin-specific stuff, whatever.
		$output->addModuleStyles( 'ext.logofunctions' );
	}

	/**
	 * Get some css for a background image for some thumbs for the target logo...
	 *
	 * @param string $logo Name of an uploaded file
	 * @param int $targetWidth
	 * @param bool $size Include width/height for actual element?
	 *
	 * @return bool|string false on failure, string of css on success
	 */
	public static function getBackground( $logo = '', $targetWidth = 0, $size = false ) {
		$config = RequestContext::getMain()->getConfig();

		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			// MediaWiki 1.34+
			$file = MediaWikiServices::getInstance()->getRepoGroup()
				->findFile( $logo );
		} else {
			$file = wfFindFile( $logo );
		}
		if ( !$file ) {
			// Let whatever called this actually handle it
			return false;
		}

		$targetWidth = ( is_numeric( $targetWidth ) && $targetWidth > 0 ) ? $targetWidth : 154;

		// Double it for hidpi support, because honestly fuck it, who cares
		$thumb = $file->createThumb( $targetWidth * 2 );

		$background = OutputPage::transformResourcePath( $config, $thumb );
		$css = "\tbackground-image: " . CSSMin::buildUrlValue( $background ) . ";\n";

		// Except since we did that now we need to figure out the nominal size here...
		$width = $file->getWidth();
		$height = $file->getHeight();
		if ( $width > $targetWidth ) {
			$height = File::scaleHeight( $width, $height, $targetWidth );
			$width = $targetWidth;
		}
		$css .= "\tbackground-size: {$width}px {$height}px;\n";

		if ( $size ) {
			$css .= "\twidth: {$width}px;\n";
			$css .= "\theight: {$height}px;\n";
		}

		return $css;
	}
}

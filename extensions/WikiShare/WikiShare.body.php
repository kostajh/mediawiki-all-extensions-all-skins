<?php

/**
 * Class file for the WikiShare extension
 *
 * @wikisharegroup Extensions
 * @license GPL-2.0-only
 */
class WikiShare {

	/**
	 * Respond to the parser hook call to invoke the 'wikishareserv' magic word
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'wikishareserv', [ self::class, 'doParserFunction' ] );
		$parser->setHook( 'wikishare', [ self::class, 'doParserHook' ] );
	}

	/**
	 * Add properties to the page based on the parser function details.
	 *
	 * @param Parser $parser
	 * @return string
	 */
	public static function doParserFunction( Parser $parser, $twitter = '' ) {
		$parser->getOutput()->setExtensionData( 'wikishareserv:twitter', $twitter );
		return '';
	}

	/**
	 * Parser hook for the <wikishare /> tag extension.
	 *
	 * @param Parser $parser
	 * @return string
	 */
	public static function doParserHook( $input, array $args, Parser $parser ) {
		global $wgWikiShare, $wgWikiShareServices;

		# Localisation for "Share"
		$share = wfMessage( 'wikishare' )->escaped();

		# Output WikiShare widget
		$output = '<!-- WikiShare Buttons BEGIN -->
			<div class="wikishare_toolbox wikishare_default_style" id="wikisharetoolbar" style="background:' .
			$wgWikiShare['background'] . '; border-color:' . $wgWikiShare['border'] . ';">';

		$output .= self::makeLinks( $wgWikiShareServices, $parser );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Function for article header share toolbar
	 *
	 * @param Article &$article
	 * @param bool &$outputDone
	 * @param bool &$pcache
	 * @return bool|bool
	 */
	public static function WikiShareHeader( &$article, &$outputDone, &$pcache ) {
		global $wgOut, $wgWikiShare, $wgWikiShareServices;

		# Check if page is in content namespace and the setting to enable/disable
		# article header tooblar either on the main page or at all
		if ( !MWNamespace::isContent( $article->getTitle()->getNamespace() )
			|| !$wgWikiShare['header']
			|| ( $article->getTitle()->equals( Title::newMainPage() ) && !$wgWikiShare['main'] )
		) {
			return true;
		}

		# Localisation for "Share"
		$share = wfMessage( 'wikishare' )->escaped();

		# Output WikiShare widget
		$wgOut->addHTML( '<!-- WikiShare Buttons BEGIN -->
			<div class="wikishare_toolbox wikishare_default_style" id="wikisharetoolbar" style="background:' .
			$wgWikiShare['background'] . '; border-color:' . $wgWikiShare['border'] . ';">' );

		$wgOut->addHTML( self::makeLinks( $wgWikiShareServices ) );

		$wgOut->addHTML( '</div>' );

		return true;
	}

	/**
	 * Function for share sidebar portlet
	 *
	 * @param Skin $skin
	 * @param Sidebar &$bar
	 * @return bool|array
	 */
	public static function WikiShareSidebar( $skin, &$bar ) {
		global $wgOut, $wgWikiShare, $wgWikiShareServices;

		# Load css stylesheet
		$wgOut->addModuleStyles( 'ext.WikiShare' );

		# Check setting to enable/disable sidebar portlet
		if ( !$wgWikiShare['sidebar'] ) {
			return true;
		}

		# Output WikiShare widget
		$bar['wikishare'] = '<!-- WikiShare Buttons BEGIN -->
			<div class="wikishare_toolbox wikishare_default_style" id="wikisharesidebar">';

		$bar['wikishare'] .= self::makeLinks( $wgWikiShareServices );

		$bar['wikishare'] .= '</div>';

		return true;
	}

	/**
	 * Function for follow sidebar portlet
	 *
	 * @param Skin $skin
	 * @param Sidebar &$bar
	 * @return bool|array
	 */
	public static function WikiShareFollowSidebar( $skin, &$bar ) {
		global $wgOut, $wgWikiShare, $wgWikiShareFollow;

		# Load css stylesheet
		$wgOut->addModuleStyles( 'ext.WikiShare' );

		# Check setting to enable/disable sidebar portlet
		if ( !$wgWikiShare['follow'] ) {
			return true;
		}

		# Output WikiShare widget
		$bar['wikishare-follow'] = '<!-- WikiShare Buttons BEGIN -->
			<div class="wikishare_follow" id="wikisharefollow">';

		$bar['wikishare-follow'] .= self::makeLinks( $wgWikiShareFollow );

		$bar['wikishare-follow'] .= '</div>';

		return true;
	}

	/**
	 * Converts an array definition of links into HTML tags
	 *
	 * @param array $links
	 * @return string
	 */
	protected static function makeLinks( $links, $parser = null ) {
		global $wgOut, $wgSitename;

		if ( $parser ) {
			$pout = $parser->getOutput();
		} else if ( $wgOut->canUseWikiPage() ) {
			$wikiPage = $wgOut->getWikiPage();
			$pout = $wikiPage->getParserOutput( $wikiPage->makeParserOptions( 'canonical' ) );
		}

		$html = '';
		$twitter = '';
		$title = $wgOut->getPageTitle();
		$twitter = $pout ? $pout->getExtensionData( 'wikishareserv:twitter' ) : null;
		if ( !$twitter ) {
		  $twitter = $wgOut->getPageTitle();
		}
		if ( $wgOut->canUseWikiPage() ) {
			foreach ( $links as $link ) {
				$attribs = $link['attribs'] ?? '';
				$pageurl = $wgOut->getTitle()->getFullURL();
				$servurl = str_replace("%url%", $pageurl, $link['url']);
				$servurl = str_replace("%title%", $title, $servurl);
				$servurl = str_replace("%twitter%", $twitter, $servurl);
				$servurl = str_replace("%wiki%", $wgSitename, $servurl);

				$html .= '<span title="Share on ' . $link['service'] . '"><a class="wikishare_button_' . $link['service'] . '" ' . $attribs . ' href="' . $servurl . '" target="_blank"><img class="wikishare_icon" src="' . $link['icon'] . '" width="20px"></a></span>';
			}
		}

		return $html;
	}
}

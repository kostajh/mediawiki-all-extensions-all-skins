<?php

use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;

class NamespacePopupsHooks {

	/**
	 * @param \MediaWiki\Linker\LinkRenderer $renderer
	 * @param LinkTarget $target
	 * @param bool $isKnown
	 * @param string|HtmlArmor &$text
	 * @param array &$attribs
	 * @param ?string &$ret HTML output
	 *
	 * @return bool
	 */
	public static function onHtmlPageLinkRendererEnd(
		$renderer,
		LinkTarget $target,
		$isKnown,
		&$text,
		array &$attribs,
		&$ret
	) {
		global $wgNamespacePopupsNamespaceMap, $wgNamespacePopupsAnchor;

		if ( !$wgNamespacePopupsNamespaceMap ) {
			return true;
		}

		$services = MediaWikiServices::getInstance();
		$subjectNsId = $services->getNamespaceInfo()->getSubject( $target->getNamespace() );
		$linkNS = $services->getContentLanguage()->getNsText( $subjectNsId ) ?: '';

		$popupNS = isset( $wgNamespacePopupsNamespaceMap[$linkNS] )
			? $wgNamespacePopupsNamespaceMap[$linkNS] : null;
		if ( !$popupNS ) {
			$popupNS = isset( $wgNamespacePopupsNamespaceMap['*'] )
				? $wgNamespacePopupsNamespaceMap['*'] : null;
		}

		if ( !$popupNS ) {
			return true;
		}

		if ( $popupNS === '*' ) {
			$popupNS = $linkNS;
		}

		$remains = $target->getDBkey();
		$anchor = $wgNamespacePopupsAnchor ? $wgNamespacePopupsAnchor : '&uarr;';
		$page = $popupNS === '' ? $remains : "$popupNS:$remains";
		$title = Title::newFromText( $page );

		if ( !$title ) {
			return true;
		}

		$classes = 'mw-pagepopup';
		if ( $title->isKnown() ) {
			$url = $title->getLocalUrl();
		} else {
			$url = $title->getLinkURL( [ 'action' => 'edit', 'redlink' => '1' ] );
			$classes .= ' new';
		}

		$html = HtmlArmor::getHtml( $text );
		$ret = Html::rawElement( 'a', $attribs, $html )
			. Html::rawElement( 'a', [ 'class' => $classes, 'href' => $url ], $anchor );

		// Stop processing the HtmlPageLinkRendererEnd hook
		return false;
	}

	/**
	 * @param Parser $parser
	 * @param string &$text
	 */
	public static function onParserAfterTidy( Parser $parser, &$text ) {
		global $wgNamespacePopupsNamespaceMap;

		if ( !$wgNamespacePopupsNamespaceMap ) {
			return;
		}

		$parserOutput = $parser->getOutput();

		$oldLinks = [];
		// The below algorithm enumerates all links. This may be a little inefficient
		foreach ( $parserOutput->getLinks() as $linkNSid => $linksArray ) {
			$linkNS = MWNamespace::getCanonicalName( $linkNSid );
			foreach ( array_keys( $linksArray ) as $remains ) {
				$oldLinks[] = [ $linkNS, $remains ];
			}
		}

		foreach ( $oldLinks as $linkInfo ) {
			[ $linkNS, $remains ] = $linkInfo;

			if ( isset( $wgNamespacePopupsNamespaceMap[$linkNS] ) ) {
				$popupNS = $wgNamespacePopupsNamespaceMap[$linkNS];
			} elseif ( isset( $wgNamespacePopupsNamespaceMap['*'] ) ) {
				$popupNS = $wgNamespacePopupsNamespaceMap['*'];
			} else {
				$popupNS = null;
			}
			if ( !$popupNS ) {
				continue;
			}
			if ( $popupNS === '*' ) {
				$popupNS = $linkNS;
			}
			$popupPage = $popupNS === '' ? $remains : "$popupNS:$remains";

			$parserOutput->addLink( Title::newFromDBkey( $popupPage ) );
		}
	}
}

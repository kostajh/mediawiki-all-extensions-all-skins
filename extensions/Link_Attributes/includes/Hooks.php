<?php

namespace MediaWiki\Extension\LinkAttributes;

use Html;
use HtmlArmor;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use Sanitizer;
use Xml;

class Hooks {

	/**
	 * @param string|HtmlArmor &$text
	 * @param array &$attribs
	 * @param int $isExternal
	 */
	protected static function modifyLink( &$text, &$attribs, $isExternal = 0 ) {
		if ( $text instanceof HtmlArmor ) {
			$text = HtmlArmor::getHtml( $text );
		}
		if ( preg_match( '/^(.+)\(\((.*)\)\)$/', $text, $matches ) ) {
			$text = trim( $matches[1] );
			$rels = preg_split( '/\s+/', $matches[ 2 ] );

			foreach ( $rels as $r ) {
				if ( $isExternal && ( strtolower( $r ) == '-nofollow' ) ) {
					continue; # Not allowed!!
				}

				if ( ( substr( $r, 0, 2 ) == '-~' || substr( $r, 0, 2 ) == '~-' ) && isset( $attribs[ 'rev' ] ) ) {
					$attribs[ 'rev' ] = str_ireplace( substr( $r, 2 ), '', $attribs[ 'rev' ] );
				} elseif ( ( substr( $r, 0, 2 ) == '-.' || substr( $r, 0, 2 ) == '.-' ) && isset( $attribs[ 'class' ] ) ) {
					$attribs[ 'class' ] = str_ireplace( substr( $r, 2 ), '', $attribs[ 'class' ] );
				} elseif ( ( substr( $r, 0, 1 ) == '-' ) && isset( $attribs[ 'rel' ] ) ) {
					$attribs[ 'rel' ] = str_ireplace( substr( $r, 1 ), '', $attribs[ 'rel' ] );
				} elseif ( substr( $r, 0, 1 ) == '~' ) {
					$attribs[ 'rev' ] .= ' ' . substr( $r, 1 );
				} elseif ( substr( $r, 0, 1 ) == '.' ) {
					$attribs[ 'class' ] .= ' ' . substr( $r, 1 );
				} else {
					$attribs[ 'rel' ] .= ' ' . $r;
				}
			}

			if ( isset( $attribs[ 'rel' ] ) ) {
				$attribs[ 'rel' ] = trim( preg_replace( '/\s+/', ' ', $attribs[ 'rel' ] ) );
			}
			if ( isset( $attribs[ 'rev' ] ) ) {
				$attribs[ 'rev' ] = trim( preg_replace( '/\s+/', ' ', $attribs[ 'rev' ] ) );
			}
			if ( isset( $attribs[ 'class' ] ) ) {
				$attribs[ 'class' ] = trim( preg_replace( '/\s+/', ' ', $attribs[ 'class' ] ) );
			}
		}
	}

	/**
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/HtmlPageLinkRendererEnd
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param bool $isKnown
	 * @param string &$text
	 * @param string[] &$attribs
	 * @param string &$ret
	 * @return bool
	 */
	public static function onHtmlPageLinkRendererEnd(
		LinkRenderer $linkRenderer, LinkTarget $target, $isKnown, &$text, &$attribs, &$ret
	) {
		static::modifyLink( $text, $attribs );
		return true;
	}

	/**
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/LinkerMakeExternalLink
	 * @param string &$url
	 * @param string &$text
	 * @param string &$link
	 * @param string[] &$attribs
	 * @param string $linktype
	 * @return bool
	 */
	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linktype ) {
		$attribsText = Html::expandAttributes( [ 'class' => 'external ' . $linktype ] );
		$mergedattribs = array_merge( $attribs, Sanitizer::decodeTagAttributes( $attribsText ) );

		static::modifyLink( $text, $mergedattribs, 1 );
		if ( $mergedattribs ) {
			$attribsText = Xml::expandAttributes( $mergedattribs );
		}
		$link = sprintf( '<a href="%s"%s>%s</a>', $url, $attribsText, $text );

		return false;
	}
}

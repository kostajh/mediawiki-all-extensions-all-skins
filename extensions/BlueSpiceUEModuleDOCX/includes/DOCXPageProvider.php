<?php
/**
 * DOCXPageProvider.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use MediaWiki\MediaWikiServices;

/**
 * UniversalExport DOCXPageProvider class.
 * @package BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 */
class DOCXPageProvider {

	/**
	 * Fetches the requested pages markup, cleans it and returns a DOMDocument.
	 * @param array $params Needs the 'article-id' key to be set and valid.
	 * @return array
	 */
	public static function getPage( $params ) {
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleDOCXbeforeGetPage',
			[
				&$params
			]
		);

		$title = Title::newFromID( $params['article-id'] );
		if ( $title == null ) {
			$title = Title::newFromText( $params['title'] );
		}

		$PCP = new BsPageContentProvider();
		$pageDOM = $PCP->getDOMDocumentContentFor( $title,
			$params + [ 'follow-redirects' => true ]
		);
		// TODO RBV (06.12.11 17:09): Follow Redirect... setting or default?

		// Cleanup DOM
		self::cleanUpDOM( $title, $pageDOM, $params );

		$DOMXPath = new DOMXPath( $pageDOM );
		$firstHeading = $DOMXPath->query( "//*[contains(@class, 'firstHeading')]" )->item( 0 );
		$bodyContent  = $DOMXPath->query( "//*[contains(@class, 'bodyContent')]" )->item( 0 );

		if ( isset( $params['display-title'] ) ) {
			$firstHeading->nodeValue = $params['display-title'];
		}

		$page = [
			'dom' => $pageDOM,
			'firstheading-element' => $firstHeading,
			'bodycontent-element'  => $bodyContent,
		];

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSUEModuleDOCXgetPage', [
			$title,
			&$page,
			&$params,
			$DOMXPath
		] );
		return $page;
	}

	/**
	 * Cleans the DOM: removes editsections, script tags, some elementy
	 * by classes, makes links absolute and pages paginatable and prevents
	 * large images from clipping in the DOCX
	 * @param Title $title
	 * @param DOMDocument $pageDOM
	 * @param array $params
	 */
	private static function cleanUpDOM( $title, $pageDOM, $params ) {
		$classesToRemove = [ 'editsection', 'bs-universalexport-exportexclude', 'magnify' ];
		$DOMXPath = new DOMXPath( $pageDOM );
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleDOCXcleanUpDOM',
			[
				$title,
				$pageDOM,
				&$params,
				$DOMXPath,
				&$classesToRemove
			]
		);

		// Remove script-Tags
		foreach ( $pageDOM->getElementsByTagName( 'script' ) as $scriptElement ) {
			$scriptElement->parentNode->removeChild( $scriptElement );
		}

		// Remove elements by class
		$containsStmnts = [];
		foreach ( $classesToRemove as $classToRemove ) {
			$containsStmnts[] = "contains(@class, '" . $classToRemove . "')";
		}

		// Remove jumpmark anchors as Word doesn't need them and they may create unwanted linebreaks
		$containsStmnts[] = "contains(@name, 'bs-ue-jumpmark-')";

		$XPath = '//*[' . implode( ' or ', $containsStmnts ) . ']';

		$elementsToRemove = $DOMXPath->query( $XPath );
		foreach ( $elementsToRemove as $elementToRemove ) {
			$elementToRemove->parentNode->removeChild( $elementToRemove );
		}

		// Make internal hyperlinks absolute
		global $wgServer;
		// No external and no jumplinks
		$internalAnchorElements = $DOMXPath->query(
			"//a[not(contains(@class, 'external')) and not(starts-with(@href, '#'))]"
		);
		foreach ( $internalAnchorElements as $internalAnchorElement ) {
			$relativePath = $internalAnchorElement->getAttribute( 'href' );
			$internalAnchorElement->setAttribute(
				'href',
				$wgServer . $relativePath
			);
		}

		// TOC is not needed as Word generates one
		$TOCULElement = $DOMXPath->query( "//*[contains(@id, 'toc')]" )->item( 0 );
		if ( $TOCULElement instanceof DOMElement ) {
			$TOCULElement->parentNode->removeChild( $TOCULElement );
		}

		$imageLinks = $DOMXPath->query( "//a[contains(@class, 'image')]" );
		foreach ( $imageLinks as $imageLink ) {
			$parent = BsDOMHelper::getParentDOMElement( $imageLink );
			$image  = BsDOMHelper::getFirstDOMElementChild( $imageLink );
			$classes = explode( ' ', $parent->getAttribute( 'class' ) );
			if ( in_array( 'thumbinner', $classes ) ) {
				$parent = BsDOMHelper::getParentDOMElement( $parent );
				$classes = explode( ' ', $parent->getAttribute( 'class' ) );
			}

			$intersect = array_intersect( $classes, [ 'floatleft', 'tleft' ] );
			if ( !empty( $intersect ) ) {
				$image->setAttribute( 'align', 'left' );
			}
			$intersect = array_intersect( $classes, [ 'floatright', 'tright' ] );
			if ( !empty( $intersect ) ) {
				$image->setAttribute( 'align', 'right' );
			}
			// $parent->parentNode->insertBefore( $image );
			// $parent->parentNode->removeChild($parent);
		}

		// TODO: Should this be in DocxServlet::findFiles()?
		// Prevent large images from clipping
		foreach ( $pageDOM->getElementsByTagName( 'img' ) as $imgElement ) {
			$width = $imgElement->getAttribute( 'width' );
			if ( $width > 700 ) {
				$imgElement->setAttribute( 'width', 700 );
				$imgElement->removeAttribute( 'height' );

				$classToRemoves = $imgElement->getAttribute( 'class' );
				$imgElement->setAttribute( 'class', $classToRemoves . ' maxwidth' );
			}

			// Remove surrounding anchor tags as PHPDOCX will render them with
			// an underline
			$parent = BsDOMHelper::getParentDOMElement( $imgElement );
			if ( strtoupper( $parent->nodeName ) !== 'A' ) { continue;
			}
			BsDOMHelper::insertAfter( $imgElement, $parent );
			$parent->parentNode->removeChild( $parent );
		}

		// PHPDOCX needs <p style="page-break-after" />
		// when using strictWordStyles and not interpreting CSS
		$pageBreaks = $DOMXPath->query( "//*[contains(@class, 'bs-universalexport-pagebreak')]" );
		// "non-live" list
		$pageBreaks = [];
		foreach ( $pageBreaks as $pageBreak ) {
			$pageBreaks[] = $pageBreak;
		}
		foreach ( $pageBreaks as $pageBreak ) {
			$newPB = $pageDOM->createElement( 'p' );
			// TODO: Maybe better set attribute on next DOMElement sibling of
			// $pageBreak and afterwards remove $pageBreak. BUT: property may
			// only be evaluated on <p> tag!?
			$newPB->setAttribute( 'style', 'page-break-before:always' );
			$pageBreak->parentNode->replaceChild( $newPB, $pageBreak );
		}

		// To avoid PHPDOCX from formatting every headline in BOLD
		// we have to and an explicit font-weight styling
		$headingSpans = $DOMXPath->query( "//*[contains(@class, 'mw-headline')]" );
		// "non-live" list
		$headingSpansList = [];
		foreach ( $headingSpans as $headingSpan ) {
			$headingSpansList[] = $headingSpan;
		}
		foreach ( $headingSpansList as $headingSpan ) {
			$headingSpan->setAttribute( 'style', 'font-weight:normal' );
		}

		// There are some MediaWiki stylings we want to make available for
		// Word formatting
		$mediaWikiClasses = $DOMXPath->query( "//*[contains(@class, 'box')]" );
		// TODO: may match "some-box" or "allboxes"
		foreach ( $mediaWikiClasses as $MWClass ) {
			$classToRemoves = $MWClass->getAttribute( 'class' );
			// TODO: May contain more than one class!
			$classes = explode( ' ', $classToRemoves );

			// Flatten UL/OLs
			// TODO: Recursive?
			$childNodes = [];
			foreach ( $MWClass->childNodes as $oChild ) {
				$childNodes[] = $oChild;
			}

			foreach ( $childNodes as $list ) {
				if ( $list instanceof DOMElement == false ) { continue;
				}
				if ( !in_array( strtoupper( $list->nodeName ), [ 'UL', 'OL' ] ) ) { continue;
				}

				$LIs = $list->getElementsByTagName( 'li' );
				foreach ( $LIs as $LI ) {
					$p = $pageDOM->createElement( 'p' );
					$list->parentNode->insertBefore( $p, $list );
					foreach ( $LI->childNodes as $oContentNode ) {
						$p->appendChild( $oContentNode );
					}
				}
				$list->parentNode->removeChild( $list );
			}

			BsDOMHelper::addClassesRecursive( $MWClass, $classes );
		}
	}
}

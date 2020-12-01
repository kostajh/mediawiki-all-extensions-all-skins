<?php
/**
 * UniversalExport PDF Module extension for BlueSpice
 *
 * Enables MediaWiki to export pages with subpages into PDF format.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @package    UEModulePDFSubpages
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Utility\UrlTitleParser;
use MediaWiki\MediaWikiServices;

/**
 * Base class for UniversalExport PDF Module extension
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDFRecursive
 */
class UEModulePDFSubpages extends BsExtensionMW {

	/**
	 * Initialization of UEModulePDFSubpages extension
	 */
	protected function initExt() {
		// Hooks
		$this->setHook(
			'ChameleonSkinTemplateOutputPageBeforeExec',
			'onSkinTemplateOutputPageBeforeExec'
		);
		$this->setHook( 'BSUEModulePDFBeforeAddingContent' );
	}

	/**
	 * Hook handler to add menu
	 * @param SkinTemplate &$oSkin
	 * @param QuickTemplate &$oTemplate
	 * @return bool Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$oSkin, &$oTemplate ) {
		if ( $oSkin->getTitle()->isContentPage() === false ) {
			return true;
		}
		if ( !MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan(
				'uemodulepdfsubpages-export',
				$oSkin->getUser(),
				$oSkin->getTitle()
			)
		) {
			return true;
		}

		$oTemplate->data['bs_export_menu'][] = $this->buildContentAction();

		return true;
	}

	/**
	 * Builds the ContentAction Array fort the current page
	 * @return array - The ContentAction Array
	 */
	private function buildContentAction() {
		$aCurrentQueryParams = $this->getRequest()->getValues();
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			$sTitle = $aCurrentQueryParams['title'];
		} else {
			$sTitle = '';
		}
		$sSpecialPageParameter = BsCore::sanitize( $sTitle, '', BsPARAMTYPE::STRING );
		$oSpecialPage = SpecialPage::getTitleFor( 'UniversalExport', $sSpecialPageParameter );
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			unset( $aCurrentQueryParams['title'] );
		}
		$aCurrentQueryParams['ue[module]'] = 'pdf';
		$aCurrentQueryParams['ue[subpages]'] = '1';

		return [
			'id' => 'pdf-subpages',
			'href' => $oSpecialPage->getLinkUrl( $aCurrentQueryParams ),
			'title' => wfMessage( 'bs-uemodulepdfsubpages-widgetlink-subpages-title' )->text(),
			'text' => wfMessage( 'bs-uemodulepdfsubpages-widgetlink-subpages-text' )->text(),
			'class' => 'bs-ue-export-link',
			'iconClass' => 'icon-file-pdf bs-ue-export-link'
		];
	}

	/**
	 *
	 * @param array &$template
	 * @param array &$contents
	 * @param \stdClass $caller
	 * @param array &$params
	 * @return bool Always true to keep hook running
	 */
	public function onBSUEModulePDFBeforeAddingContent(
		&$template,
		&$contents,
		$caller,
		&$params = []
	) {
		global $wgRequest;
		$ueParams = $caller->aParams;

		if ( empty( $ueParams ) ) {
			$requestParams = $wgRequest->getArray( 'ue' );
			$ueParams['subpages'] = isset( $requestParams['subpages'] ) ? $requestParams['subpages'] : 0;
		}

		if ( $ueParams['subpages'] == 0 ) {
			return true;
		}

		$newDOM = new DOMDocument();
		$pageDOM = $contents['content'][0];
		$pageDOM->setAttribute(
			'class',
			$pageDOM->getAttribute( 'class' ) . ' bs-source-page'
		);
		$node = $newDOM->importNode( $pageDOM, true );

		$includedTitleMap = [];
		$rootTitle = \Title::newFromText( $template['title-element']->nodeValue );
		if ( $pageDOM->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'id' ) === '' ) {
			$pageDOM->getElementsByTagName( 'a' )->item( 0 )->setAttribute(
				'id',
				md5( $rootTitle->getPrefixedText() )
			);
		}

		$includedTitleMap[$template['title-element']->nodeValue]
			= $pageDOM->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'id' );

		$newDOM->appendChild( $node );

		$includedTitles = $this->findSubpages( $caller );
		if ( count( $includedTitles ) < 1 ) {
			return true;
		}

		$titleMap = array_merge(
			$includedTitleMap,
			$this->generateIncludedTitlesMap( $includedTitles )
		);

		$this->setIncludedTitlesId( $includedTitles, $titleMap );
		$this->addIncludedTitlesContent( $includedTitles, $titleMap, $contents['content'] );

		foreach ( $contents['content'] as $oDom ) {
			$this->rewriteLinks( $oDom, $titleMap );
		}

		$this->makeBookmarks( $template, $includedTitles );

		$documentToc = $this->makeToc( $titleMap );
		array_unshift( $contents, $documentToc->documentElement );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'UEModulePDFSubpagesAfterContent',
			[
				$this,
				&$contents
			]
		);

		return true;
	}

	/**
	 *
	 * @param array $includedTitles
	 * @param array $includedTitleMap
	 * @param array &$contents
	 */
	private function addIncludedTitlesContent( $includedTitles, $includedTitleMap, &$contents ) {
		foreach ( $includedTitles as $name => $content ) {
			$contents[] = $content['dom']->documentElement;
		}
	}

	/**
	 *
	 * @param array $includedTitles
	 * @return array
	 */
	private function generateIncludedTitlesMap( $includedTitles ) {
		$includedTitleMap = [];

		foreach ( $includedTitles as $name => $content ) {
			$includedTitleMap = array_merge( $includedTitleMap, [ $name => md5( $name ) ] );
		}

		return $includedTitleMap;
	}

	/**
	 *
	 * @param array &$includedTitles
	 * @param array $includedTitleMap
	 */
	private function setIncludedTitlesId( &$includedTitles, $includedTitleMap ) {
		foreach ( $includedTitles as $name => $content ) {
			// set array index from $includedTitleMap
			$documentLinks = $content['dom']->getElementsByTagName( 'a' );
			if ( $documentLinks->item( 0 ) instanceof DOMElement ) {
				$documentLinks->item( 0 )->setAttribute(
					'id',
					$includedTitleMap[$name]
				);
			}
		}
	}

	/**
	 *
	 * @param \stdClass $caller
	 * @return array
	 */
	private function findSubpages( $caller ) {
		$linkdedTitles = [];

		$subpages = $caller->oRequestedTitle->getSubpages();

		foreach ( $subpages as $title ) {
			$pageProvider = new BsPDFPageProvider();
			$pageContent = $pageProvider->getPage( [
				'article-id' => $title->getArticleID(),
				'title' => $title->getFullText()
			] );

			if ( !isset( $pageContent['dom'] ) ) {
				continue;
			}

			$linkdedTitles = array_merge(
				$linkdedTitles,
				[
					$title->getPrefixedText() => $pageContent
				]
			);
		}

		ksort( $linkdedTitles );

		return $linkdedTitles;
	}

	/**
	 *
	 * @param array &$template
	 * @param array $includedTitles
	 */
	private function makeBookmarks( &$template, $includedTitles ) {
		foreach ( $includedTitles as $name => $content ) {
			$bookmarkNode = BsUniversalExportHelper::getBookmarkElementForPageDOM( $content['dom'] );
			$bookmarkNode = $template['dom']->importNode( $bookmarkNode, true );

			$template['bookmarks-element']->appendChild( $bookmarkNode );
		}
	}

	/**
	 *
	 * @param DOMNode &$domNode
	 * @param array $linkMap
	 */
	protected function rewriteLinks( &$domNode, $linkMap ) {
		$anchors = $domNode->getElementsByTagName( 'a' );
		foreach ( $anchors as $anchor ) {
			$linkTitle = $anchor->getAttribute( 'data-bs-title' );
			$href  = $anchor->getAttribute( 'href' );

			if ( $linkTitle ) {
				$pathBasename = str_replace( '_', ' ', $linkTitle );

				$parsedHref = parse_url( $href );

				if ( isset( $linkMap[$pathBasename] ) && isset( $parsedHref['fragment'] ) ) {
					$linkMap[$pathBasename] = $linkMap[$pathBasename] . '-' . md5( $parsedHref['fragment'] );
				}
			} else {
				$class = $anchor->getAttribute( 'class' );

				if ( empty( $href ) ) {
					// Jumplink targets
					continue;
				}

				$classes = explode( ' ', $class );
				if ( in_array( 'external', $classes ) ) {
					continue;
				}

				$parsedHref = parse_url( $href );
				if ( !isset( $parsedHref['path'] ) ) {
					continue;
				}

				$parser = new UrlTitleParser(
					$href, MediaWikiServices::getInstance()->getMainConfig(), true
				);
				$parsedTitle = $parser->parseTitle();

				if ( !$parsedTitle instanceof Title ) {
					continue;
				}

				$pathBasename = $parsedTitle->getPrefixedText();
			}

			if ( !isset( $linkMap[$pathBasename] ) ) {
				continue;
			}

			$anchor->setAttribute( 'href', '#' . $linkMap[$pathBasename] );
		}
	}

	/**
	 * @param array $linkMap
	 * @return DOMDocument
	 */
	protected function makeTOC( $linkMap ) {
		$tocDocument = new DOMDocument();

		$tocWrapper = $tocDocument->createElement( 'div' );
		$tocWrapper->setAttribute( 'class', 'bs-page-content bs-page-toc' );

		$tocHeading = $tocDocument->createElement( 'h1' );
		$tocHeading->appendChild( $tocDocument->createTextNode( wfMessage( 'toc' )->text() ) );

		$tocWrapper->appendChild( $tocHeading );

		$tocList = $tocDocument->createElement( 'ul' );
		$tocList->setAttribute( 'class', 'toc' );

		$count = 1;
		foreach ( $linkMap as $linkname => $linkHref ) {
			$liClass = 'toclevel-1';
			if ( $count === 1 ) {
				$liClass .= ' bs-source-page';
			}
			$tocListItem = $tocList->appendChild( $tocDocument->createElement( 'li' ) );
			$tocListItem->setAttribute( 'class', $liClass );

			$tocListItemLink = $tocListItem->appendChild( $tocDocument->createElement( 'a' ) );
			$tocListItemLink->setAttribute( 'href', '#' . $linkHref );
			$tocListItemLink->setAttribute( 'class', 'toc-link' );

			$tocLinkSpanNumber = $tocListItemLink->appendChild( $tocDocument->createElement( 'span' ) );
			$tocLinkSpanNumber->setAttribute( 'class', 'tocnumber' );
			$tocLinkSpanNumber->appendChild( $tocDocument->createTextNode( $count . '.' ) );

			$tocListSpanText = $tocListItemLink->appendChild( $tocDocument->createElement( 'span' ) );
			$tocListSpanText->setAttribute( 'class', 'toctext' );
			$tocListSpanText->appendChild( $tocDocument->createTextNode( ' ' . $linkname ) );

			$count++;
		}
		$tocWrapper->appendChild( $tocList );
		$tocDocument->appendChild( $tocWrapper );

		return $tocDocument;
	}
}

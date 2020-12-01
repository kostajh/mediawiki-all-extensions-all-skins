<?php
/**
 * UniversalExport Recursive PDF Module extension for BlueSpice
 *
 * Enables MediaWiki to export pages into PDF format.
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
 * @package    BlueSpice_Extensions
 * @subpackage UEModulePDFRecursive
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - Initial release
 */

use BlueSpice\Utility\UrlTitleParser;
use MediaWiki\MediaWikiServices;

/**
 * Base class for UniversalExport PDF Module extension
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDFRecursive
 */
class UEModulePDFRecursive extends BsExtensionMW {

	/**
	 * Initialization of UEModulePDFRecursive extension
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
	 * @param SkinTemplate &$skin
	 * @param QuickTemplate &$template
	 * @return bool Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$skin, &$template ) {
		$title = $skin->getTitle();
		if ( $title->isContentPage() === false ) {
			return true;
		}
		if ( !MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'uemodulepdfrecursive-export', $skin->getUser(), $title )
		) {
			return true;
		}

		$template->data['bs_export_menu'][] = $this->buildContentAction();

		return true;
	}

	/**
	 * builds the contentAction array for the current page
	 *
	 * @return array contentAction
	 * @throws MWException
	 */
	private function buildContentAction() {
		$currentQueryParams = $this->getRequest()->getValues();
		if ( isset( $currentQueryParams['title'] ) ) {
			$title = $currentQueryParams['title'];
		} else {
			$title = '';
		}
		$specialPageParameter = BsCore::sanitize( $title, '', BsPARAMTYPE::STRING );
		$specialPage = SpecialPage::getTitleFor( 'UniversalExport', $specialPageParameter );
		if ( isset( $currentQueryParams['title'] ) ) {
			unset( $currentQueryParams['title'] );
		}
		$currentQueryParams['ue[module]'] = 'pdf';
		$currentQueryParams['ue[recursive]'] = '1';

		return [
			'id' => 'pdf-recursive',
			'href' => $specialPage->getLinkUrl( $currentQueryParams ),
			'title' => wfMessage( 'bs-uemodulepdfrecursive-widgetlink-recursive-title' )->text(),
			'text' => wfMessage( 'bs-uemodulepdfrecursive-widgetlink-recursive-text' )->text(),
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
			$ueParams['recursive'] = isset( $requestParams['recursive'] ) ? $requestParams['recursive'] : 0;
		}

		if ( $ueParams['recursive'] == 0 ) {
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

		$includedTitleMap[ $template['title-element']->nodeValue ]
			= $pageDOM->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'id' );

		$newDOM->appendChild( $node );

		$includedTitles = $this->findLinkedTitles( $pageDOM );
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
			'UEModulePDFRecursiveAfterContent',
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
	 * @param DOMDocument $rootTitleDom
	 * @return array
	 */
	private function findLinkedTitles( $rootTitleDom ) {
		$linkdedTitles = [];

		$links = $rootTitleDom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			$class = $link->getAttribute( 'class' );
			$classes = explode( ' ', $class );
			$excludeClasses = [ 'new', 'external' ];

			// HINT: http://stackoverflow.com/questions/7542694/in-array-multiple-values
			if ( count( array_intersect( $classes, $excludeClasses ) ) > 0 ) {
				continue;
			}

			$linkTitle = $link->getAttribute( 'data-bs-title' );
			if ( empty( $linkTitle ) || empty( $link->nodeValue ) ) {
				continue;
			}

			$title = Title::newFromText( $linkTitle );
			if ( $title == null || !$title->canExist() ) {
				continue;
			}

			// Avoid double export
			if ( in_array( $title->getPrefixedText(), $linkdedTitles ) ) {
				continue;
			}

			if ( !$title->userCan( 'read' ) ) {
				continue;
			}

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

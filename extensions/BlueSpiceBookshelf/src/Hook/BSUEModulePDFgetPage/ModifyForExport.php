<?php

namespace BlueSpice\Bookshelf\Hook\BSUEModulePDFgetPage;

use BlueSpice\UEModulePDF\Hook\BSUEModulePDFgetPage;
use Exception;
use MediaWiki\MediaWikiServices;
use PageHierarchyProvider;
use Title;

class ModifyForExport extends BSUEModulePDFgetPage {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$bUserNumberHeadings = (bool)$this->getContext()->getUser()->getOption( 'numberheadings' );
		// TODO RBV (10.02.12 16:35): Use Hook "BSUEModulePDFcleanUpDOM"
		// Remove <bs:bookshelf ... /> generated Markup
		$oBookshelfTagContainerElements =
			$this->DOMXPath->query( "//*[contains(@class, 'bs-bookshelf-toc')]" );
		foreach ( $oBookshelfTagContainerElements as $oBookshelfTagContainerElement ) {
			$oBookshelfTagContainerElement->parentNode
				->removeChild( $oBookshelfTagContainerElement );
		}

		$sRequestedTitle = $this->title->getPrefixedText();
		$sDisplayTitle = $sRequestedTitle;
		try {
			if ( isset( $this->params['php'] ) && isset( $this->params['php']['title'] ) ) {
				$phpf = MediaWikiServices::getInstance()->getService(
					'BSBookshelfPageHierarchyProviderFactory'
				);
				$oPHP = $phpf->getInstanceFor( $this->params['php']['title'], $this->params['php'] );

			} else {
				$oPHP = PageHierarchyProvider::getInstanceForArticle( $sRequestedTitle );
			}

			$oEntry = $oPHP->getEntryFor( $sRequestedTitle );
			$sNumber = $oEntry->articleNumber;

			if ( isset( $oEntry->articleDisplayTitle ) ) {
				$sDisplayTitle = $oEntry->articleDisplayTitle;
			}
			// Fallback in case of no display title but subpage
			if ( str_replace( '_', ' ', $sDisplayTitle ) === $sRequestedTitle
				&& $this->title->isSubpage() ) {
				$sDisplayTitle = basename( $this->title->getText() );
			}
			$bHasChildren = isset( $oEntry->children ) && !empty( $oEntry->children );

			$this->page['number'] = $sNumber;

			// Add number to headlines
			$sClassesToPrefix = [
				'bs-ue-document-title',
				'firstHeading',
				// ATTENTION: In old MW this was 'mw-headline', but this now matches
				// with 'mw-headline-number'
				'mw-headline-number',
				'tocnumber'
			];

			$sClassXpath = implode( "') or contains(@class, '", $sClassesToPrefix );
			$sClassXpath = "contains(@class, '" . $sClassXpath . "')";

			$oHeadlineElements = $this->DOMXPath->query( "//*[$sClassXpath]" );
			foreach ( $oHeadlineElements as $oHeadlineElement ) {
				if ( $oHeadlineElement->firstChild === null ) { continue;
				}

				$sSeperator = '.';
				$sCssClass = $oHeadlineElement->getAttribute( 'class' );
				if ( in_array( $sCssClass, [ 'bs-ue-document-title', 'firstHeading' ] ) ) {
					$sSeperator = ' ';
				}
				if ( $sSeperator === '.' && $bUserNumberHeadings === false ) {
					// No numberation for internal headings
					continue;
				}

				if ( $sSeperator === '.' && $bHasChildren === true ) {
					// Avoid number collision with child node articles
					continue;
				}

				$numNode = $this->page['dom']->createElement( 'span', $sNumber . $sSeperator );
				$numNode->setAttribute( 'class', 'bs-chapter-number' );

				$oHeadlineElement->insertBefore(
					$numNode,
					$oHeadlineElement->firstChild
				);
			}

			// Modify heading nodes
			if ( $bUserNumberHeadings === true && $bHasChildren === false ) {
				$BookmarkElements = $this->page['bookmark-element']->getElementsByTagName( 'bookmark' );
				foreach ( $BookmarkElements as $oBookmarkElement ) {
					$sName = $oBookmarkElement->getAttribute( 'name' );
					$oBookmarkElement->setAttribute( 'name', $sNumber . '.' . $sName );
				}
			}

			// Modify page title node
			$this->page['bookmark-element']->setAttribute(
				'name', $sNumber . ' ' . $this->page['bookmark-element']->getAttribute( 'name' )
			);

			$aAncestors = $oPHP->getAncestorsFor( $sRequestedTitle );
			$this->createRunningHeader( $this->page, $aAncestors, $oPHP->getBookMeta() );
		}
		catch ( Exception $e ) {
			// No bookshelf tag? Well in this case we do not need to take any action...
			wfDebugLog(
				'BS::Bookshelf',
				'onBSUEModulePDFgetPage: Error: ' . $e->getMessage()
			);
		}
		return true;
	}

	/**
	 *
	 * @param array $page
	 * @param array $aAncestors
	 * @param array $aBookMeta
	 * @return null
	 */
	protected function createRunningHeader( $page, $aAncestors, $aBookMeta ) {
		$oSourceTitle = Title::newFromText( $aAncestors['sourcearticletitle'] );

		if ( isset( $aBookMeta['title'] ) && !empty( $aBookMeta['title'] ) ) {
			$sSourceTitle = $aBookMeta['title'];
		} elseif ( $this->getConfig()->get( 'BookshelfSupressBookNS' ) ) {
			$sSourceTitle = $oSourceTitle->getText();
		} else {
			$sSourceTitle = $oSourceTitle->getPrefixedText();
		}

		$oRunningHeader = $this->page['dom']->createElement( 'div' );
		$oRunningHeader->setAttribute( 'class', 'bs-runningheader' );
		$this->page['bodycontent-element']->parentNode->insertBefore(
			$oRunningHeader, $this->page['bodycontent-element']
		);

		$oBookTitle = $this->page['dom']->createElement( 'div' );
		$oSourceTextNode = $this->page['dom']->createTextNode( $sSourceTitle );
		$oBookTitle->appendChild( $oSourceTextNode );
		$oBookTitle->setAttribute( 'class', 'bs-booktitle' );

		$oAncestorTable = $this->page['dom']->createElement( 'table' );
		$oAncestorTR = $oAncestorTable->appendChild( $this->page['dom']->createElement( 'tr' ) );
		$oRunningHeader->appendChild( $oAncestorTable );

		$oAncestorTD = $oAncestorTR->appendChild( $this->page['dom']->createElement( 'td' ) );
		$oAncestorTD->setAttribute( 'class', 'bs-ancestors-left' );
		$oAncestorTD->appendChild( $oBookTitle );

		if ( empty( $aAncestors['ancestors'] ) ) {
			// If there are no ancestors we don't need to create a second TD
			return;
		}

		$oChapterAncestors = $this->page['dom']->createElement( 'div' );
		$oChapterAncestors->setAttribute( 'class', 'bs-ancestors' );

		foreach ( $aAncestors['ancestors'] as $aAncestor ) {
			$oChapterAncestor = $this->page['dom']->createElement( 'div' );
			$oChapterAncestor->setAttribute( 'class', 'bs-ancestor' );

			$sNumberedAncestorTitle = $aAncestor['number'] . '. ' . $aAncestor['display-title'];
			$oAncestorElement = $this->page['dom']->createTextNode( $sNumberedAncestorTitle );

			$oChapterAncestor->appendChild( $oAncestorElement );
			$oChapterAncestors->appendChild( $oChapterAncestor );
		}

		$oAncestorTD = $oAncestorTR->appendChild( $this->page['dom']->createElement( 'td' ) );
		$oAncestorTD->setAttribute( 'class', 'bs-ancestors-right' );
		$oAncestorTD->appendChild( $oChapterAncestors );
	}

}

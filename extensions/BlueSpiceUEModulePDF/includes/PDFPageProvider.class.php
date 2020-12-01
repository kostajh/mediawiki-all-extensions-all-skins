<?php
/**
 * BsPDFPageProvider.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 *
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use MediaWiki\MediaWikiServices;

/**
 * UniversalExport BsPDFPageProvider class.
 * @package BlueSpiceUEModulePDF
 */
class BsPDFPageProvider {

	/**
	 * Fetches the requested pages markup, cleans it and returns a DOMDocument.
	 * @param array $aParams Needs the 'article-id' or 'title' key to be set and valid.
	 * @return array
	 */
	public static function getPage( $aParams ) {
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSUEModulePDFbeforeGetPage', [
			&$aParams
		] );

		$oBookmarksDOM = new DOMDocument();
		$oBookmarksDOM->loadXML( '<bookmarks></bookmarks>' );

		$oTitle = null;
		if ( isset( $aParams['article-id'] ) ) {
			$oTitle = Title::newFromID( $aParams['article-id'] );
		}
		if ( $oTitle == null && isset( $aParams['title'] ) ) {
			// HINT: This is probably the wrong place for urldecode(); Should be
			// done by caller. I.e. BookExportModulePDF
			$oTitle = Title::newFromText( urldecode( $aParams['title'] ) );
		}
		if ( !$oTitle ) {
			$id = isset( $aParams['article-id'] ) ? $aParams['article-id'] : 0;
			$text = isset( $aParams['title'] ) ? $aParams['title'] : '';
			throw new Exception(
				"Could not create valid Title object from id '$id' or text '$text'!"
			);
		}

		$oPCP = new BsPageContentProvider();
		$oPageDOM = $oPCP->getDOMDocumentContentFor(
			$oTitle,
			$aParams + [ 'follow-redirects' => true ]
		); // TODO RBV (06.12.11 17:09): Follow Redirect... setting or default?

		// Collect Metadata
		$aData = self::collectData( $oTitle, $oPageDOM, $aParams );

		// Cleanup DOM
		self::cleanUpDOM( $oTitle, $oPageDOM, $aParams );

		$oBookmarkNode = BsUniversalExportHelper::getBookmarkElementForPageDOM( $oPageDOM );
		// HINT: http://www.mm-newmedia.de/blog/2010/05/wrong-document-error-wtf/
		$oBookmarksDOM->documentElement->appendChild(
			$oBookmarksDOM->importNode( $oBookmarkNode, true )
		);

		$oDOMXPath = new DOMXPath( $oPageDOM );
		$oFirstHeading = $oDOMXPath->query( "//*[contains(@class, 'firstHeading')]" )->item( 0 );
		$oBodyContent  = $oDOMXPath->query( "//*[contains(@class, 'bodyContent')]" )->item( 0 );

		// TODO RBV (01.02.12 11:28): What if no TOC?
		$oTOCULElement = $oDOMXPath->query( "//*[contains(@class, 'toc')]//ul" )->item( 0 );

		if ( isset( $aParams['display-title'] ) ) {
			$oBookmarkNode->setAttribute( 'name', $aParams['display-title'] );
			$oTitleText = $oFirstHeading->ownerDocument->createTextNode( $aParams['display-title'] );
			$oFirstHeading->nodeValue = '';
			$oFirstHeading->replaceChild( $oTitleText, $oFirstHeading->firstChild );
			$aData['meta']['title'] = $aParams['display-title'];
		}

		$aPage = [
			'resources' => $aData['resources'],
			'dom' => $oPageDOM,
			'firstheading-element' => $oFirstHeading,
			'bodycontent-element'  => $oBodyContent,
			'toc-ul-element'   => $oTOCULElement,
			'bookmarks-dom'    => $oBookmarksDOM,
			'bookmark-element' => $oBookmarkNode,
			'meta'             => $aData['meta']
		];

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSUEModulePDFgetPage', [
			$oTitle,
			&$aPage,
			&$aParams,
			$oDOMXPath
		] );
		return $aPage;
	}

	/**
	 * Collects metadata and additional resources for this page
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams
	 * @return array array( 'meta' => ..., 'resources' => ...);
	 */
	private static function collectData( $oTitle, $oPageDOM, $aParams ) {
		$aMeta      = [];
		$aResources = [
			'ATTACHMENT' => [],
			'STYLESHEET' => [],
			'IMAGE' => []
		];

		// TODO RBV (01.02.12 13:51): Handle oldid
		$aCategories = [];
		if ( $oTitle->exists() ) {
			// TODO RBV (27.06.12 11:47): Throws an exception. Maybe better use try ... catch instead of $oTitle->exists()
			$aAPIParams = new FauxRequest( [
					'action' => 'parse',
					// 'oldid'  => ,
					'page'  => $oTitle->getPrefixedText(),
					'prop'   => 'images|categories|links'
			] );

			try {
				$oAPI = new ApiMain( $aAPIParams );
				$oAPI->execute();
			} catch ( Exception $e ) {
				$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig(
					'bsg'
				);
				// allow the PDFExport to export error messages and exceptions
				// this is only really beeing used when a collection such as a
				// book or page with supages is exported and the user may does
				// not have permission for one particualr page, the user will
				// then still receive the file, but instead of the content
				// there will be an error message like "you dont have permission"
				if ( !$config->get( 'UEModulePDFAllowPartialExport' ) ) {
					throw $e;
				}
			}

			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				$aResult = $oAPI->getResult()->getResultData( null, [
					'BC' => [],
					'Types' => [],
					'Strip' => 'all',
				] );
			} else {
				$aResult = $oAPI->getResultData();
			}

			foreach ( $aResult['parse']['categories'] as $aCat ) {
				$aCategories[] = $aCat['*'];
			}
		}
		/*
		//For future use...
		$localRepo = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo();
		foreach($aResult['parse']['images'] as $sFileName ) {
			$oImage = $localRepo->newFile( Title::newFromText( $sFileName, NS_FILE ) );
			if( $oImage->exists() ) {
				$sAbsoluteFileSystemPath = $oImage->getFullPath();
			}
		}
		 */

		// Dublin Core:
		$aMeta['DC.title'] = $oTitle->getPrefixedText();
		$aMeta['DC.date']  = wfTimestamp( TS_ISO_8601 ); // TODO RBV (14.12.10 14:01): Check for conformity. Maybe there is a better way to acquire than wfTimestamp()?

		// Custom
		global $wgLang;
		$oDOMXPath = new DOMXPath( $oPageDOM );
		$domTitles = $oDOMXPath->query( "//*[contains(@class, 'firstHeading')]" );
		$sTitle = "";
		foreach ( $domTitles as $domTitle ) {
			$sTitle = $domTitle->nodeValue;
		}
		$sCurrentTS = $wgLang->userAdjust( wfTimestampNow() );
		$aMeta[ 'title' ] = empty( $sTitle ) ? $oTitle->getPrefixedText() : $sTitle;
		$aMeta[ 'pagetitle' ] = $oTitle->getPrefixedText();
		$aMeta['exportdate']      = $wgLang->sprintfDate( 'd.m.Y', $sCurrentTS );
		$aMeta['exporttime']      = $wgLang->sprintfDate( 'H:i', $sCurrentTS );
		$aMeta['exporttimeexact'] = $wgLang->sprintfDate( 'H:i:s', $sCurrentTS );

		// Custom - Categories->Keywords
		$aMeta['keywords'] = implode( ', ', $aCategories );

		$oMetadataElements = $oDOMXPath->query( "//div[@class='bs-universalexport-meta']" );
		foreach ( $oMetadataElements as $oMetadataElement ) {
			if ( $oMetadataElement->hasAttributes() ) {
				foreach ( $oMetadataElement->attributes as $oAttribute ) {
					if ( $oAttribute->name !== 'class' ) {
						$aMeta[ $oAttribute->name ] = $oAttribute->value;
					}
				}
			}
			$oMetadataElement->parentNode->removeChild( $oMetadataElement );
		}

		// If it's a normal article
		if ( !in_array( $oTitle->getNamespace(), [ NS_SPECIAL, NS_FILE, NS_CATEGORY ] ) ) {
			$oWikiPage = WikiPage::factory( $oTitle );
			$aMeta['author'] = $oWikiPage->getUserText(); // TODO RBV (14.12.10 12:19): Realname/Username -> DisplayName
			$aMeta['date']   = $wgLang->sprintfDate( 'd.m.Y', $oWikiPage->getTouched() );
		}

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFcollectMetaData',
			[
				$oTitle,
				$oPageDOM,
				&$aParams,
				$oDOMXPath,
				&$aMeta
			]
		);

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );
		$aMetaDataOverrides = json_decode(
			$config->get( 'UniversalExportMetadataOverrides' ),
			true
		);
		$aMeta = array_merge( $aMeta, $aMetaDataOverrides );

		return [
			'meta'      => $aMeta,
			'resources' => $aResources
		];
	}

	/**
	 * Cleans the DOM: removes editsections, script tags, some elementy
	 * by classes, makes links absolute and pages paginatable and prevents
	 * large images from clipping in the PDF
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams
	 */
	private static function cleanUpDOM( $oTitle, $oPageDOM, $aParams ) {
		global $wgServer;
		$aClassesToRemove = [ 'editsection', 'bs-universalexport-exportexclude' ];
		$oDOMXPath = new DOMXPath( $oPageDOM );
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSUEModulePDFcleanUpDOM', [
			$oTitle,
			$oPageDOM,
			&$aParams,
			$oDOMXPath,
			&$aClassesToRemove
		] );

		// Remove script-Tags
		foreach ( $oPageDOM->getElementsByTagName( 'script' ) as $oScriptElement ) {
			$oScriptElement->parentNode->removeChild( $oScriptElement );
		}

		// Remove elements by class
		$aContainsStmnts = [];
		foreach ( $aClassesToRemove as $sClass ) {
			$aContainsStmnts[] = "contains(@class, '" . $sClass . "')";
		}
		$sXPath = '//*[' . implode( ' or ', $aContainsStmnts ) . ']';

		$oEditSectionElements = $oDOMXPath->query( $sXPath );
		foreach ( $oEditSectionElements as $oEditSectionElement ) {
			$oEditSectionElement->parentNode->removeChild( $oEditSectionElement );
		}

		// Make internal hyperlinks absolute
		// No external-, interwiki- and jumplinks
		$oInternalAnchorElements = $oDOMXPath->query(
			"//a[not(contains(@class, 'external')) and not(contains(@class, 'extiw')) "
			. "and not(starts-with(@href, '#'))]"
		);
		foreach ( $oInternalAnchorElements as $oInternalAnchorElement ) {
			$sRelativePath = $oInternalAnchorElement->getAttribute( 'href' );
			$oInternalAnchorElement->setAttribute(
				'href',
				$wgServer . $sRelativePath
			);
		}

		/**
		 * Since, most likely, mail links will not be clickable
		 * in the PDF, at least show the address
		 * @var DOMNodeList $mailAnchors
		 */
		$mailAnchors = $oDOMXPath->query(
			"//a[contains(@class, 'external') and starts-with(@href, 'mailto:')]"
		);

		/** @var DOMElement $mailAnchor */
		foreach ( $mailAnchors as $mailAnchor ) {
			$label = $mailAnchor->nodeValue;
			$href = $mailAnchor->getAttribute( 'href' );
			$address = str_replace( 'mailto:', '', $href );

			$labelIsValid = !preg_match( '/^\[\d*\]$/', $label );
			if ( $labelIsValid ) {
				$address = "$label: $address";
			}

			$mailAnchor->nodeValue = $address;
		}

		// <editor-fold defaultstate="collapsed" desc="Reference Tags">
		// TODO RBV (31.01.12 17:17): This should be in an extra extension like CiteConnector!
		// $oReferenceTags = $oDOMXPath->query( "//a[contains(@class, 'references')]" );
		// Old Code from Book.class.php
		/*
			$sOut = preg_replace_callback(
						'#<ol class="references">(.*?)</ol>#si',
						array( $this, 'tranformReferenceTags'),
						$sOut
					);

		protected function tranformReferenceTags( $matches ) {
			$referencesOl = '<hr /><ol class="references">';

			$listBody = preg_replace_callback(
									'#(<li.*?>)(.*?)</li>#si',
									array( $this, 'processListItems' ),
									$matches[1] );

			$referencesOl .= $listBody.'</ol>';
			return $referencesOl;
		}

		private function processListItems( $matches ) {
			$listItemStartTag = $matches[1];
			$listItemContent  = $matches[2];

			$startOfSupTag = strpos( $listItemContent, '<sup>' );
			if ( $startOfSupTag ) {
				$listItemContent = substr( $listItemContent, $startOfSupTag );
			}
			else {
				$sUp = $this->mI18N->msg('reference-tag-up');
				$listItemContent = preg_replace('#(<a.*?>).*?</a>(.*?)$#', '\2 \1('.$sUp.')</a>', $listItemContent );
			}

			return $listItemStartTag.$listItemContent.'</li>';
		}*/
		// </editor-fold>

		// Make tables paginatable
		$oTableElements = $oPageDOM->getElementsByTagName( 'table' );
		foreach ( $oTableElements as $oTableElement ) {
			self::correctTableWidth( $oTableElement );
			self::duplicateTableHeadsForPagination( $oTableElement, $oPageDOM );
		}

		// Prevent "first page empty" bug
		$oBodyContent = $oDOMXPath->query( "//*[contains(@class, 'bodyContent')]" )->item( 0 );
		if ( $oBodyContent ) {
			$oAntiBugP = $oPageDOM->createElement( 'p' );
			$oAntiBugP->nodeValue = 'I am here to prevent the first-page-empty bug!';
			$oAntiBugP->setAttribute( 'style', 'visibility:hidden;height:0px;margin:0px;padding:0px' );
			$oBodyContent->insertBefore( $oAntiBugP, $oBodyContent->firstChild );
		}
	}

	/**
	 *
	 * @param DOMElement $oTableElement
	 */
	protected static function correctTableWidth( $oTableElement ) {
		$sClassAttribute = $oTableElement->getAttribute( 'class' );
		$oTableElement->setAttribute( 'class', $sClassAttribute . ' bs-correct-table-width ' );
	}

	/**
	 *
	 * @param DOMElement $oTableElement
	 * @param array &$aBodys
	 */
	protected static function findTableBodys( $oTableElement, &$aBodys ) {
		$oTableBodys = $oTableElement->childNodes; // We only want direct children, so we cannot use getElementsByTagName
		foreach ( $oTableBodys as $oTableBody ) {
			// Filter for <tbody>
			if ( $oTableBody instanceof DOMElement && $oTableBody->tagName == 'tbody' ) {
				$aBodys[] = $oTableBody;
			}
		}
	}

	/**
	 *
	 * @param DOMElement $oTableBody
	 * @param array &$aRows
	 */
	protected static function findTableRows( $oTableBody, &$aRows ) {
		$oTableRows = $oTableBody->childNodes; // We only want direct children, so we cannot use getElementsByTagName
		foreach ( $oTableRows as $oTableRow ) {
			// Filter for <tr>
			if ( $oTableRow instanceof DOMElement && $oTableRow->tagName == 'tr' ) {
			$aRows[] = $oTableRow;
			}
		}
	}

	/**
	 *
	 * @param DOMElement &$oTableElement
	 * @param DOMDocument &$oPageDOM
	 * @param array $aRows
	 * @param DOMElement &$oTHead
	 * @param DOMElement &$oTBody
	 */
	protected static function findTableHeads( &$oTableElement, &$oPageDOM, $aRows, &$oTHead, &$oTBody ) {
		foreach ( $aRows as $oTableRow ) {
			// TODO RBV (06.02.12 15:07): Examine behavior when TH in row with TDs
			$oTHs = $oTableRow->getElementsByTagName( 'th' );

			if ( $oTHs->length != 0 ) {
				if ( $oTBody->hasChildNodes() ) {
					$oTableElement->appendChild( $oTBody );
					$oTBody = $oPageDOM->createElement( 'tbody' );
				}
				$oTHead->appendChild( $oTableRow );
			} else {
				if ( $oTHead->hasChildNodes() ) {
					$oTableElement->appendChild( $oTHead );
					$oTHead = $oPageDOM->createElement( 'thead' );
				}
				$oTBody->appendChild( $oTableRow );
			}
		}
	}

	/**
	 *
	 * @param DOMElement &$oTableElement
	 * @param DOMDocument &$oPageDOM
	 */
	protected static function duplicateTableHeadsForPagination( &$oTableElement, &$oPageDOM ) {
		$aBodys = [];
		self::findTableBodys( $oTableElement, $aBodys );

		foreach ( $aBodys as $oTableBody ) {
			$aRows = [];
			self::findTableRows( $oTableBody, $aRows );

			$oTHead = $oPageDOM->createElement( 'thead' );
			$oTBody = $oPageDOM->createElement( 'tbody' );

			self::findTableHeads( $oTableElement, $oPageDOM, $aRows, $oTHead, $oTBody );

			if ( $oTHead->hasChildNodes() ) {
					$oTableElement->appendChild( $oTHead );
			}
			if ( $oTBody->hasChildNodes() ) {
				$oTableElement->appendChild( $oTBody );
			}
		}
	}
}

<?php
/**
 * ExportModuleHTML.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage UEModuleHTML
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use MediaWiki\MediaWikiServices;

/**
 * UniversalExport ExportModuleHTML class.
 * @package BlueSpice_Extensions
 * @subpackage UEModuleHTML
 */
class ExportModuleHTML implements BsUniversalExportModule {

	/**
	 * Implementation of BsUniversalExportModule interface. Uses the
	 * Java library xhtmlrenderer to create a HTML file.
	 * @param SpecialUniversalExport &$caller
	 * @return array array(
	 * 'mime-type' => 'application/zip',
	 * 'filename' => 'Filename.zip',
	 * 'content' => '8F3BC3025A7...'
	 * );
	 */
	public function createExportFile( &$caller ) {
		global $wgRequest;
		$pageParams = $caller->aParams;

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		$pageParams['title']      = $caller->oRequestedTitle->getPrefixedText();
		$pageParams['article-id'] = $caller->oRequestedTitle->getArticleID();
		$pageParams['oldid']      = $wgRequest->getInt( 'oldid', 0 );
		if ( $config->get( 'UEModuleHTMLSuppressNS' ) ) {
			$pageParams['display-title'] = $caller->oRequestedTitle->getText();
		}
		// If we are in history mode and we are relative to an oldid
		$pageParams['direction'] = $wgRequest->getVal( 'direction', '' );
		if ( !empty( $pageParams['direction'] ) ) {
			$lookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$currentRevision = $lookup->getRevisionById( $pageParams['oldid'] );
			switch ( $pageParams['direction'] ) {
				case 'next':
					$currentRevision = $lookup->getNextRevision(
						$currentRevision
					);
					break;
				case 'prev':
					$currentRevision = $lookup->getPreviousRevision(
						$currentRevision
					);
					break;
				default:
					break;
			}
			if ( $currentRevision !== null ) {
				$pageParams['oldid'] = $currentRevision->getId();
			}
		}

		// Get Page DOM
		$pageDOM = BsPDFPageProvider::getPage( $pageParams );

		// Prepare Template
		$templateParams = [
			'path'     => $config->get( 'UEModulePDFTemplatePath' ),
			'template' => $config->get( 'UEModulePDFDefaultTemplate' ),
			'language' => $caller->getUser()->getOption( 'language', 'en' ),
			'meta'     => $pageDOM['meta']
		];

		// Override template param if needed.
		// The override may come from GET (&ue[template]=...)
		// or from a tag (<bs:ueparams template="..." />)
		// TODO: Make more generic
		if ( !empty( $caller->aParams['template'] ) ) {
			$templateParams['template'] = $caller->aParams['template'];
		}

		// TODO: decouple from UEModulePDF
		$template = BsPDFTemplateProvider::getTemplate( $templateParams );

		// Combine Page Contents and Template
		$DOM = $template['dom'];

		// Add the bookmarks
		$template['bookmarks-element']->appendChild(
			$template['dom']->importNode( $pageDOM['bookmark-element'], true )
		);
		$template['title-element']->nodeValue = $caller->oRequestedTitle->getPrefixedText();

		$contents = [
			'content' => [ $pageDOM['dom']->documentElement ]
		];
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleHTMLBeforeAddingContent',
			[
				&$template,
				&$contents
			]
		);

		$contentTags = $DOM->getElementsByTagName( 'content' );
		$i = $contentTags->length - 1;
		while ( $i > -1 ) {
			$contentTag = $contentTags->item( $i );
			$key = $contentTag->getAttribute( 'key' );
			if ( isset( $contents[$key] ) ) {
				foreach ( $contents[$key] as $node ) {
					$node = $DOM->importNode( $node, true );
					$contentTag->parentNode->insertBefore( $node, $contentTag );
				}
			}
			$contentTag->parentNode->removeChild( $contentTag );
			$i--;
		}

		$caller->aParams['document-token'] = md5( $caller->oRequestedTitle->getPrefixedText() )
			. '-' . $caller->aParams['oldid'];
		$caller->aParams['title'] = $caller->oRequestedTitle->getText();
		$caller->aParams['resources']      = $template['resources'];

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleHTMLBeforeCreateHTML',
			[
				$this,
				$DOM,
				$caller
			]
		);

		// Prepare response
		$response = [
			'mime-type' => 'application/zip',
			'filename'  => '%s.zip',
			'content'   => ''
		];

		if ( RequestContext::getMain()->getRequest()->getVal( 'debugformat', '' ) == 'html' ) {
			$response['content'] = $DOM->saveXML( $DOM->documentElement );
			$response['mime-type'] = 'text/html';
			$response['filename'] = sprintf(
				'%s.html',
				$caller->oRequestedTitle->getPrefixedText()
			);
			$response['disposition'] = 'inline';
			return $response;
		}

		$this->modifyPDFSpecificStuff( $caller->aParams, $DOM );

		$HTMLArchiver = new HTMLArchiver( $caller->aParams );
		$response['content'] = $HTMLArchiver->createHTML( $DOM );

		$response['filename'] = sprintf(
			$response['filename'],
			$caller->oRequestedTitle->getPrefixedText()
		);

		return $response;
	}

	/**
	 * Implementation of BsUniversalExportModule interface. Creates an overview
	 * over the PdfExportModule
	 * @return ViewExportModuleOverview
	 */
	public function getOverview() {
		$moduleOverviewView = new ViewExportModuleOverview();

		$moduleOverviewView->setOption(
			'module-title',
			wfMessage( 'bs-uemodulehtml-overview-title' )->plain()
		);
		$moduleOverviewView->setOption(
			'module-description',
			wfMessage( 'bs-uemodulehtml-overview-description' )->plain()
		);
		$moduleOverviewView->setOption( 'module-bodycontent', '' );

		return $moduleOverviewView;
	}

	/**
	 * This is really ugly! As we use the same templates as UEModulePDF we have
	 * to change some stuff, like embedded fonts and running elements...
	 * @param array &$params
	 * @param /DOMDocument $DOM
	 */
	public function modifyPDFSpecificStuff( &$params, $DOM ) {
		// Remove fonts
		foreach ( $params['resources']['STYLESHEET'] as $key => $sValue ) {
			if ( strtoupper( substr( $params['resources']['STYLESHEET'][$key], -3 ) ) == 'TTF' ) {
				unset( $params['resources']['STYLESHEET'][$key] );
			}
		}

		// Remove <bookmarks>
		$bookmarksElement = $DOM->getElementsByTagName( 'bookmarks' )->item( 0 );
		$bookmarksElement->parentNode->removeChild( $bookmarksElement );

		$DOMXPath = new DOMXPath( $DOM );
		$runningElements = $DOMXPath->query(
			"//*[starts-with(@id, 'bs-running')] | //*[contains(@class, 'bs-running')]"
		);
		$runningElementsList = [];
		foreach ( $runningElements as $RElem ) {
			$runningElementsList[] = $RElem;
		}
		foreach ( $runningElementsList as $RElem ) {
			$RElem->parentNode->removeChild( $RElem );
		}
	}
}

<?php
/**
 * BsExportModulePDF.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use BlueSpice\UniversalExport\ExportModule;
use MediaWiki\MediaWikiServices;

/**
 * UniversalExport BsExportModulePDF class.
 * @package BlueSpiceUEModulePDF
 */
class BsExportModulePDF extends ExportModule {

	/**
	 * Implementation of BsUniversalExportModule interface. Uses the
	 * Java library xhtmlrenderer to create a PDF file.
	 * @param SpecialUniversalExport &$oCaller
	 * @return array array( 'mime-type' => 'application/pdf', 'filename' => 'Filename.pdf', 'content' => '8F3BC3025A7...' );
	 */
	public function createExportFile( &$oCaller ) {
		global $wgRequest;
		$aPageParams = $oCaller->aParams;

		if ( $oCaller->oRequestedTitle->userCan( 'uemodulepdf-export' ) === false ) {
			throw new PermissionsError( 'uemodulepdf-export' );
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		$aPageParams['title']      = $oCaller->oRequestedTitle->getPrefixedText();
		$aPageParams['article-id'] = $oCaller->oRequestedTitle->getArticleID();
		$aPageParams['oldid']      = $wgRequest->getInt( 'oldid', 0 );

		$redirectTarget = WikiPage::factory( $oCaller->oRequestedTitle )->getRedirectTarget();
		if ( $redirectTarget instanceof Title ) {
			$aPageParams['title'] = $redirectTarget->getPrefixedText();
			$aPageParams['article-id'] = $redirectTarget->getArticleID();
		}

		if ( $config->get( 'UEModulePDFSuppressNS' ) ) {
			$aPageParams['display-title'] = $oCaller->oRequestedTitle->getText();
		}
		// If we are in history mode and we are relative to an oldid
		$aPageParams['direction'] = $wgRequest->getVal( 'direction', '' );
		if ( !empty( $aPageParams['direction'] ) ) {
			$lookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$oCurrentRevision = $lookup->getRevisionById( $aPageParams['oldid'] );
			switch ( $aPageParams['direction'] ) {
				case 'next':
					$oCurrentRevision = $lookup->getNextRevision(
						$oCurrentRevision
					);
					break;
				case 'prev':
					$oCurrentRevision = $lookup->getPreviousRevision(
						$oCurrentRevision
					);
					break;
				default:
					break;
			}
			if ( $oCurrentRevision !== null ) {
				$aPageParams['oldid'] = $oCurrentRevision->getId();
			}
		}

		// Get Page DOM
		$aPage = BsPDFPageProvider::getPage( $aPageParams );

		// Prepare Template
		$aTemplateParams = [
			'path'     => $config->get( 'UEModulePDFTemplatePath' ),
			'template' => $config->get( 'UEModulePDFDefaultTemplate' ),
			'language' => $oCaller->getUser()->getOption( 'language', 'en' ),
			'meta'     => $aPage['meta']
		];

		// Override template param if needed. The override may come from GET (&ue[template]=...) or from a tag (<bs:ueparams template="..." />)
		// TODO: Make more generic
		if ( !empty( $oCaller->aParams['template'] ) ) {
			$aTemplateParams['template'] = $oCaller->aParams['template'];
		}

		$aTemplate = BsPDFTemplateProvider::getTemplate( $aTemplateParams );

		// Combine Page Contents and Template
		$oDOM = $aTemplate['dom'];

		// Add the bookmarks
		$aTemplate['bookmarks-element']->appendChild(
			$aTemplate['dom']->importNode( $aPage['bookmark-element'], true )
		);
		$aTemplate['title-element']->nodeValue = $oCaller->oRequestedTitle->getPrefixedText();

		$aContents = [
			'content' => [ $aPage['dom']->documentElement ]
		];
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFBeforeAddingContent',
			[
				&$aTemplate,
				&$aContents,
				$oCaller,
				&$aPage
			]
		);

		$oContentTags = $oDOM->getElementsByTagName( 'content' );
		$i = $oContentTags->length - 1;
		while ( $i > -1 ) {
			$oContentTag = $oContentTags->item( $i );
			$sKey = $oContentTag->getAttribute( 'key' );
			if ( isset( $aContents[$sKey] ) ) {
				foreach ( $aContents[$sKey] as $oNode ) {
					$oNode = $oDOM->importNode( $oNode, true );
					$oContentTag->parentNode->insertBefore( $oNode, $oContentTag );
				}
			}
			$oContentTag->parentNode->removeChild( $oContentTag );
			$i--;
		}

		$oCaller->aParams['document-token']   = md5( $oCaller->oRequestedTitle->getPrefixedText() ) . '-' . $oCaller->aParams['oldid'];
		$oCaller->aParams['soap-service-url'] = $config->get( 'UEModulePDFPdfServiceURL' );
		$oCaller->aParams['backend-url']      = $config->get( 'UEModulePDFPdfServiceURL' ); // Duplicate to replace 'soap-service-url' in future
		$oCaller->aParams['resources']        = $aTemplate['resources'];

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModulePDFBeforeCreatePDF',
			[
				$this,
				$oDOM,
				$oCaller
			]
		);

		// Prepare response
		$aResponse = [
			'mime-type' => 'application/pdf',
			'filename'  => '%s.pdf',
			'content'   => ''
		];

		if ( RequestContext::getMain()->getRequest()->getVal( 'debugformat', '' ) == 'html' ) {
			$aResponse['content'] = $oDOM->saveXML( $oDOM->documentElement );
			$aResponse['mime-type'] = 'text/html';
			$aResponse['filename'] = sprintf(
				'%s.html',
				$oCaller->oRequestedTitle->getPrefixedText()
			);
			$aResponse['disposition'] = 'inline';
			return $aResponse;
		}

		$oPDFBackend = new BsPDFServlet( $oCaller->aParams );
		$aResponse['content'] = $oPDFBackend->createPDF( $oDOM );

		$aResponse['filename'] = sprintf(
			$aResponse['filename'],
			$oCaller->oRequestedTitle->getPrefixedText()
		);

		return $aResponse;
	}

	/**
	 * Implementation of BsUniversalExportModule interface. Creates an overview
	 * over the PdfExportModule
	 * @return ViewExportModuleOverview
	 */
	public function getOverview() {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );
		$oModuleOverviewView = new ViewExportModuleOverview();

		$oModuleOverviewView->setOption( 'module-title', wfMessage( 'bs-uemodulepdf-overview-title' )->plain() );
		$oModuleOverviewView->setOption( 'module-description', wfMessage( 'bs-uemodulepdf-overview-desc' )->plain() );
		$oModuleOverviewView->setOption( 'module-bodycontent', '' );

		$oWebserviceStateView = new ViewBaseElement();
		$oWebserviceStateView->setTemplate(
			'{LABEL}: <span style="font-weight: bold; color:{COLOR}">{STATE}</span>'
			);

		$sWebServiceUrl = $config->get( 'UEModulePDFPdfServiceURL' );
		$sWebserviceState = wfMessage( 'bs-uemodulepdf-overview-webservice-state-not-ok' )->plain();
		$sColor = 'red';
		if ( BsConnectionHelper::testUrlForTimeout( $sWebServiceUrl ) ) {
			$sColor = 'green';
			$sWebserviceState = wfMessage( 'bs-uemodulepdf-overview-webservice-state-ok' )->plain();

			$oWebserviceUrlView = new ViewBaseElement();
			$oWebserviceUrlView->setTemplate(
				'{LABEL}: <a href="{URL}" target="_blank">{URL}</a><br/>'
			);
			$oWebserviceUrlView->addData( [
				'LABEL' => wfMessage( 'bs-uemodulepdf-overview-webservice-webadmin' )->plain(),
				'URL' => $sWebServiceUrl,
			] );
			$oModuleOverviewView->addItem( $oWebserviceUrlView );
		}

		$oWebserviceStateView->addData( [
			'LABEL' => wfMessage( 'bs-uemodulepdf-overview-webservice-state' )->plain(),
			'COLOR' => $sColor,
			'STATE' => $sWebserviceState
		] );

		$oModuleOverviewView->addItem( $oWebserviceStateView );

		return $oModuleOverviewView;
	}
}

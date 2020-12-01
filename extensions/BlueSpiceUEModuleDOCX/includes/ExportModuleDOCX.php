<?php
/**
 * ExportModuleDOCX.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    $Id: ExportModuleDOCX.php 9017 2013-03-25 09:22:09Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use BlueSpice\UniversalExport\ExportModule;
use MediaWiki\MediaWikiServices;

/**
 * UniversalExport ExportModuleDOCX class.
 * @package BlueSpice_Extensions
 * @subpackage UEModuleDOCX
 */
class ExportModuleDOCX extends ExportModule {

	/**
	 * Implementation of BsUniversalExportModule interface.
	 * @param SpecialUniversalExport &$caller
	 * @return array array(
	 * 'mime-type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	 * 'filename' => 'Filename.docx', 'content' => '8F3BC3025A7...'
	 * );
	 */
	public function createExportFile( &$caller ) {
		global $wgRequest;

		$caller->aParams['title']         = $caller->oRequestedTitle->getPrefixedText();
		$caller->aParams['display-title'] = $caller->oRequestedTitle->getPrefixedText();
		$caller->aParams['article-id'] = $caller->oRequestedTitle->getArticleID();
		$caller->aParams['oldid']      = $wgRequest->getInt( 'oldid', 0 );

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		if ( $config->get( 'UEModuleDOCXSuppressNS' ) ) {
			$caller->aParams['display-title'] = $caller->oRequestedTitle->getText();
		}
		// If we are in history mode and we are relative to an oldid
		$caller->aParams['direction'] = $wgRequest->getVal( 'direction', '' );
		if ( !empty( $caller->aParams['direction'] ) ) {
			$lookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$currentRevision = $lookup->getRevisionById( $caller->aParams['oldid'] );
			switch ( $caller->aParams['direction'] ) {
				case 'next':
					$currentRevision = $lookup->getNextRevision(
						$currentRevision
					);
					break;
				case 'prev':
					$currentRevision = $lookup->getNextRevision(
						$currentRevision
					);
					break;
				default:
					break;
			}
			if ( $currentRevision !== null ) {
				$caller->aParams['oldid'] = $currentRevision->getId();
			}
		}

		$caller->aParams['document-token'] = md5( $caller->oRequestedTitle->getPrefixedText() )
			. '-'
			. $caller->aParams['oldid'];
		$caller->aParams['backend-url'] = $config->get(
			'UEModuleDOCXDOCXServiceURL'
		);

		$template = $config->get( 'UEModuleDOCXTemplatePath' )
			. '/'
			. $config->get( 'UEModuleDOCXDefaultTemplate' );

		$template = realpath( $template );

		$page = DOCXPageProvider::getPage( $caller->aParams );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSUEModuleDOCXBeforeCreateDOCX',
			[
				$this,
				&$template,
				$caller
			]
		);

		$DOCXBackend = new DOCXServlet( $caller->aParams );

		// Prepare response
		$response = [
			'mime-type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'filename'  => '%s.docx',
			'content'   => ''
		];

		if ( RequestContext::getMain()->getRequest()->getVal( 'debugformat', '' ) == 'html' ) {
			$response['content'] = $page['dom']->saveHTML();
			$response['mime-type'] = 'text/html';
			$response['filename'] = sprintf(
				'%s.html',
				$caller->oRequestedTitle->getPrefixedText()
			);
			$response['disposition'] = 'inline';
			return $response;
		}

		$response['content'] = $DOCXBackend->createDOCX( $page['dom'], $template );

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

		$moduleOverviewView->setOption( 'module-title', wfMessage( 'bs-uemoduledocx-overview-title' )
			->text() );
		$moduleOverviewView
			->setOption( 'module-description', wfMessage( 'bs-uemoduledocx-overview-description' )
			->text() );
		$moduleOverviewView->setOption( 'module-bodycontent', '' );

		$webserviceStateView = new ViewBaseElement();
		$webserviceStateView->setTemplate(
			'{LABEL}: <span style="font-weight: bold; color:{COLOR}">{STATE}</span>'
			);

		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		$webServiceUrl = $config->get( 'UEModuleDOCXDOCXServiceURL' );
		$webserviceState = wfMessage( 'bs-uemoduledocx-overview-webservice-state-not-ok' )->plain();
		$color = 'red';
		if ( BsConnectionHelper::testUrlForTimeout( $webServiceUrl ) ) {
			$color = 'green';
			$webserviceState = wfMessage( 'bs-uemoduledocx-overview-webservice-state-ok' )->plain();

			$webserviceUrlView = new ViewBaseElement();
			$webserviceUrlView->setTemplate(
				'{LABEL}: <a href="{URL}" target="_blank">{URL}</a><br/>'
			);
			$webserviceUrlView->addData( [
				'LABEL' => wfMessage( 'bs-uemoduledocx-overview-webservice-webadmin' )->plain(),
				'URL'   => $webServiceUrl,
			] );
			$moduleOverviewView->addItem( $webserviceUrlView );
		}

		$webserviceStateView->addData( [
			'LABEL' => wfMessage( 'bs-uemoduledocx-overview-webservice-state' )->plain(),
			'COLOR' => $color,
			'STATE' => $webserviceState
		] );

		$moduleOverviewView->addItem( $webserviceStateView );

		return $moduleOverviewView;
	}
}

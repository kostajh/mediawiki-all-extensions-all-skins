<?php

namespace BlueSpice\UEModulePDF\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddUEModulePDF extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function skipProcessing() {
		if ( $this->skin->getTitle()->isSpecialPage() ) {
			return true;
		}

		return !$this->getServices()->getPermissionManager()->userCan(
			'uemodulepdf-export',
			$this->skin->getUser(),
			$this->skin->getTitle()
		);
	}

	protected function doProcess() {
		$this->mergeSkinDataArray(
				SkinData::EXPORT_MENU,
				[
					20 => $this->buildContentAction()
				]
		);

		return true;
	}

	/**
	 * Builds the ContentAction Array fort the current page
	 * @return array The ContentAction Array
	 */
	private function buildContentAction() {
		$aCurrentQueryParams = $this->skin->getRequest()->getValues();
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			$sTitle = $aCurrentQueryParams['title'];
		} else {
			$sTitle = '';
		}
		$sSpecialPageParameter = \BsCore::sanitize( $sTitle, '', \BsPARAMTYPE::STRING );
		$oSpecialPage = \SpecialPage::getTitleFor( 'UniversalExport', $sSpecialPageParameter );
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			unset( $aCurrentQueryParams['title'] );
		}
		$aCurrentQueryParams['ue[module]'] = 'pdf';
		return [
			'id' => 'bs-ta-uemodulepdf',
			'href' => $oSpecialPage->getLinkUrl( $aCurrentQueryParams ),
			'title' => wfMessage( 'bs-uemodulepdf-widgetlink-single-no-attachments-title' )->text(),
			'text' => wfMessage( 'bs-uemodulepdf-widgetlink-single-no-attachments-text' )->text(),
			'class' => 'bs-ue-export-link',
			'iconClass' => 'icon-file-pdf bs-ue-export-link'
		];
	}
}

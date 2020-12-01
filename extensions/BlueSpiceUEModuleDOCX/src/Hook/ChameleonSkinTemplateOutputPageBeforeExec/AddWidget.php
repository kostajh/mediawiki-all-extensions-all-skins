<?php

namespace BlueSpice\UEModuleDOCX\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

class AddWidget extends ChameleonSkinTemplateOutputPageBeforeExec {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return !$this->getServices()->getSpecialPageFactory()->exists( 'UniversalExport' );
	}

	protected function doProcess() {
		$currentQueryParams = $this->getContext()->getRequest()->getValues();
		$currentQueryParams['ue[module]'] = 'docx';
		$title = '';
		if ( isset( $currentQueryParams['title'] ) ) {
			$title = $currentQueryParams['title'];
			unset( $currentQueryParams['title'] );
		}
		$specialPage = $this->getServices()->getSpecialPageFactory()->getPage(
			'UniversalExport'
		);
		$contentActions = [
			'id' => 'docx',
			'href' => $specialPage->getPageTitle( $title )->getLinkUrl( $currentQueryParams ),
			'title' => $this->msg( 'bs-uemoduledocx-widgetlink-single-title' )->plain(),
			'text' => $this->msg( 'bs-uemoduledocx-widgetlink-single-text' )->plain(),
			'class' => 'bs-ue-export-link',
			'iconClass' => 'icon-file-word'
		];

		$this->template->data['bs_export_menu'][] = $contentActions;
	}

}

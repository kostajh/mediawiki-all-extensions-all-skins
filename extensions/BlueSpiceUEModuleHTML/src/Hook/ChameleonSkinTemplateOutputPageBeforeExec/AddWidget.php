<?php

namespace BlueSpice\UEModuleHTML\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

class AddWidget extends ChameleonSkinTemplateOutputPageBeforeExec {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->skin->getTitle()->isContentPage() ) {
			return true;
		}
		return !$this->getServices()->getSpecialPageFactory()->exists( 'UniversalExport' );
	}

	protected function doProcess() {
		$currentQueryParams = $this->getContext()->getRequest()->getValues();
		$currentQueryParams['ue[module]'] = 'html';
		$title = '';
		if ( isset( $currentQueryParams['title'] ) ) {
			$title = $currentQueryParams['title'];
			unset( $currentQueryParams['title'] );
		}
		$specialPage = $this->getServices()->getSpecialPageFactory()->getPage(
			'UniversalExport'
		);
		$contentActions = [
			'id' => 'bs-ta-uemodulehtml',
			'href' => $specialPage->getPageTitle( $title )->getLinkUrl( $currentQueryParams ),
			'title' => $this->msg( 'bs-uemodulehtml-widgetlink-single-title' )->plain(),
			'text' => $this->msg( 'bs-uemodulehtml-widgetlink-single-text' )->plain(),
			'class' => 'bs-ue-export-link',
			'iconClass' => 'icon-file-zip bs-ue-export-link'
		];

		$this->template->data['bs_export_menu'][] = $contentActions;
	}

}

<?php

namespace BlueSpice\Expiry\Hook\BSUEModulePDFgetPage;

use BlueSpice\UEModulePDF\Hook\BSUEModulePDFgetPage;

class AddPDFWatermarkClass extends BSUEModulePDFgetPage {

	protected function skipProcessing() {
		if ( !$this->getConfig()->get( 'ExpiryEnablePDFWatermark' ) ) {
			return true;
		}
		$expiry = \BlueSpice\Expiry\Extension::getExpiryForPage(
			$this->title->getArticleID()
		);
		if ( !$expiry ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$query = "//div[contains(@class, 'bs-page-content')]";
		foreach ( $this->DOMXPath->query( $query ) as $element ) {
			$element->setAttribute(
				'class',
				$element->getAttribute( 'class' ) . ' expired'
			);
		}
		return true;
	}
}

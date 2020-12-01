<?php

namespace BlueSpice\Expiry\Hook\BSUEModulePDFBeforeAddingStyleBlocks;

use BlueSpice\UEModulePDF\Hook\BSUEModulePDFBeforeAddingStyleBlocks;

class AddPDFWatermarkStyles extends BSUEModulePDFBeforeAddingStyleBlocks {

	protected function skipProcessing() {
		if ( !$this->getConfig( 'ExpiryEnablePDFWatermark' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		list( $lang ) = explode(
			'-',
			\RequestContext::getMain()->getUser()->getOption( 'language' )
		);
		$img = "{$GLOBALS['IP']}/extensions/BlueSpiceExpiry/resources/images/bg-expired-$lang.png";
		if ( !file_exists( $img ) ) {
			$lang = 'en';
			$img = "{$GLOBALS['IP']}/extensions/BlueSpiceExpiry/resources/images/bg-expired-$lang.png";
		}
		$this->template['resources']['IMAGE']["bg-expired-$lang.png"] = $img;

		$this->styleBlocks[ 'Expiry' ] = <<<HEREDOC
.bs-page-content.expired {
	background-image: url('images/bg-expired-$lang.png');
	background-repeat: no-repeat;
	background-position: top;
	background-size: 17.5cm 23.7cm;
}
HEREDOC;
		return true;
	}

}

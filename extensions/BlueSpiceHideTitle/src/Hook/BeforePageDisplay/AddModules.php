<?php

namespace BlueSpice\HideTitle\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$hideTitlePageProp = $this->getServices()->getService( 'BSUtilityFactory' )
			->getPagePropHelper( $this->out->getTitle() )->getPageProp( 'bs_hidetitle' );
		if ( $hideTitlePageProp === null ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.hidetitle.styles' );
		return true;
	}

}

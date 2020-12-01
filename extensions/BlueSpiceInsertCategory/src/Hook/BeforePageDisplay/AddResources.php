<?php

namespace BlueSpice\InsertCategory\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.insertcategory.styles' );
		$this->out->addModules( 'ext.bluespice.insertcategory' );

		if ( $this->getConfig()->get( 'InsertCategoryUploadPanelIntegration' ) ) {
			$this->out->addModules(
				'ext.bluespice.insertCategory.uploadPanelIntegration'
			);
		}
	}

}

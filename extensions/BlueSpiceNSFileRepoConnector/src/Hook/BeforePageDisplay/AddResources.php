<?php

namespace BlueSpice\NSFileRepoConnector\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.NSFRC' );
		$this->out->addModuleStyles( 'ext.bluespice.NSFRC.insertFile.styles' );
		$this->out->addModules( 'ext.bluespice.NSFRC.insertFile' );
		$this->out->addModules( 'ext.bluespice.NSFRC.BS.grid.FileRepo' );
		$this->out->addModules( 'ext.bluespice.NSFRC.multiUpload' );
		$this->out->addModules( 'mediawiki.Title.newFromImg' );
	}

}

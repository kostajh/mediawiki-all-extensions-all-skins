<?php

namespace BlueSpice\Bookshelf\Hook\BeforePageDisplay;

use BlueSpice\Services;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.bookshelf.addToBook' );

		$this->out->addModuleStyles( 'ext.bluespice.bookshelf.ui.styles' );
		$this->out->addModuleStyles( 'ext.bluespice.bookshelf.navigationTab.styles' );
		$this->out->addModules( 'ext.bluespice.bookshelf.navigationTab' );
		$this->out->addModuleStyles( 'ext.bluespice.bookshelf.pager.navigation.styles' );

		$config = Services::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$pagerBeforeContent = $config->get( 'BookShelfShowChapterNavigationPagerBeforeContent' );
		$pagerAfterContent = $config->get( 'BookShelfShowChapterNavigationPagerAfterContent' );

		if ( ( $pagerBeforeContent === true ) || ( $pagerBeforeContent === 1 ) ) {
			$this->out->addModuleStyles( 'ext.bluespice.bookshelf.pager.before-content.styles' );
		}

		if ( ( $pagerAfterContent === true ) || ( $pagerAfterContent === 1 ) ) {
			$this->out->addModuleStyles( 'ext.bluespice.bookshelf.pager.after-content.styles' );
		}
	}

}

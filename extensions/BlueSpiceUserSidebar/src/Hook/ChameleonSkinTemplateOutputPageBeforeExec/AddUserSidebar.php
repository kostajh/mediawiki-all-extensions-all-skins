<?php

namespace BlueSpice\UserSidebar\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\UserSidebar\Panel\UserSidebarNav;

class AddUserSidebar extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		return $this->getContext()->getUser()->isAnon();
	}

	protected function doProcess() {
		$this->skin->getOutput()->addModuleStyles( 'ext.blueSpiceUserSidebar.styles' );
		$this->addSiteNavTab();

		return true;
	}

	protected function addSiteNavTab() {
		$this->mergeSkinDataArray(
			SkinData::SITE_NAV,
			[
				'bs-usersidebar' => [
					'position' => 30,
					'callback' => function ( $sktemplate ) {
						return new UserSidebarNav( $sktemplate );
					}
				]
			]
		);
	}

}

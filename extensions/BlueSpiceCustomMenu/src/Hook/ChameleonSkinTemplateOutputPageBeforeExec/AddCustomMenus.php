<?php

namespace BlueSpice\CustomMenu\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

class AddCustomMenus extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$factory = $this->getServices()->getService( 'BSCustomMenuFactory' );
		$menus = [];
		foreach ( $factory->getAllMenus() as $menu ) {
			$menus[$menu->getKey()] = $menu->getRenderer()->render();
		}

		$this->mergeSkinDataArray(
			\BlueSpice\SkinData::CUSTOM_MENU,
			$menus
		);

		return true;
	}

}

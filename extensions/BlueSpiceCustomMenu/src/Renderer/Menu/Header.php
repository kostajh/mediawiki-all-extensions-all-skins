<?php

namespace BlueSpice\CustomMenu\Renderer\Menu;

class Header extends \BlueSpice\CustomMenu\Renderer\Menu {

	/**
	 *
	 * @return string
	 */
	protected function makeItemRendererKey() {
		return 'custommenuheaderitem';
	}
}

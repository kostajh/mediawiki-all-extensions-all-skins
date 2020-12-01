<?php

namespace BlueSpice\Calumma\Renderer\CustomMenu;

class Menu extends \BlueSpice\CustomMenu\Renderer\Menu {

	protected function makeItemRendererKey() {
		return 'calummacustommenuitem';
	}

}

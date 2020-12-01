<?php

namespace BlueSpice\Calumma\CustomMenu;

use MediaWiki\MediaWikiServices;

class Header extends \BlueSpice\CustomMenu\CustomMenu\Header {

	/**
	 *
	 * @return type
	 */
	public function getRenderer() {
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'calummacustommenu',
			$this->getParams()
		);
	}

}

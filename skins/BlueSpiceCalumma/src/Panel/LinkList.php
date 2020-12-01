<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\Components\SimpleLinkListGroup;
use BlueSpice\Calumma\SkinDataPanel;

class LinkList extends SkinDataPanel {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$linkListGroup = new SimpleLinkListGroup( $this->definition['content'] );
		return $linkListGroup->getHtml();
	}
}
